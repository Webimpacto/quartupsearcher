<?php

class QU_XwsClient_Search {

    private $swWS;
    private $aConfig;
    private $validate=false;
    private	$oClient=null;
    private	$datos=array();
    private $debug=0;

// para guardar datos de precios/stocks de formato 'all'
    static private $aaCache = array();

    /**
     * Inicia sesion, asi como valida usuario quartup y posteriormente carga la variable de sesion del quartup
     * Carga configuració a partir de QU_XwsClient_local.ini
     *
     * @param integer   $debug 		Nivel de debug 0:no | 1:printa los datos recibidos | 2: printa request i response | 3: printa todo el stacktrace
     * @param mixed     $iniConfig	Archivo 'ini' de configuración, opcional, por defecto usa 'QU_XwsClient.ini', o bien array de configuración
     * @param boolean   $swWS       Indicador de si queremos usar webservices (true, por defecto), o trabajar a pelo (false, mejorando drásticamente el rendimiento)
     * @return QU_XwsClient_Search
     */
    function __construct($debug=0, $iniConfig='QU_XwsClient_local.ini.php', $swWS=true){
        $this->swWS  = $swWS;
        $this->debug = $debug;

        // si el '$iniConfig' es un array, la usamos como la '$aConfig' directamente
        // (más cómodo para casos en que los valores son dinámicos, y no queremos fichero 'ini')
        if (is_array($iniConfig)) {
            $aConfig = $iniConfig;
        } else {
            include $iniConfig;
            $aConfig = $soapcli;
        }
        // corregimos valores opcionales
        if (! isset($aConfig['cache'])) {
            $aConfig['cache'] = 0;
        }
        if (! isset($aConfig['certificate'])) {
            $aConfig['certificate'] = 'no';
        }
        if (! isset($aConfig['emp_quartup'])) {
            $aConfig['emp_quartup'] = '';
        }

        if ($this->swWS) {
            $validacion = $this->openNusoapClient($aConfig);
            if ($this->debug>=1) {
                if (substr($validacion,0,2)=="ok")
                    echo "<br>Validación OK: {$validacion}<br>";
                else
                    echo "<br>Validación FAIL: {$validacion}<br>";
            }
        } else {
            $this->aConfig = $aConfig;
            $this->validate = true;
        }
    }

    private function openNusoapClient ($aConfig) {
        require_once 'lib/nusoap.php';

        // como en la primera llamada el servidor no recibe las 'cookies', se las enviamos por 'GET'
        $url_srv = $aConfig['url_srv']
            .'&user='.$aConfig['usr_quartup']
            .'&empr='.$aConfig['emp_quartup']
        ;

        ini_set('soap.wsdl_cache_enabled', $aConfig['cache']);
        $this->oClient = new nusoap_client($url_srv, 'wsdl');
        if ($aConfig['certificate']=='yes')
        {
            $this->oClient->authtype = 'certificate';
            $this->oClient->certRequest['sslcertfile']=$aConfig['rutacert'];
            $this->oClient->certRequest['sslkeyfile']=$aConfig['rutacert'];
            if ($aConfig['passphrase']!='')
                $this->oClient->certRequest['passphrase']=$aConfig['passphrase'];
        }
        $this->oClient->setDebugLevel($this->debug);
        if ($this->debug==1)
        {
            $err = $this->oClient->getError();
            if ($err) {
                echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                echo '<pre>';var_dump($this->oClient);echo '</pre>';
            }
        }

        // asignamos 'cookies' de usuario y empresa para codificar las sesiones
        $this->oClient->setCookie('user',$aConfig['usr_quartup']);
        $this->oClient->setCookie('empr',$aConfig['emp_quartup']);

        $validacion = $this->oClient->call('validate',array('usuario'=>$aConfig['usr_quartup'],'pass'=>$aConfig['pass_quartup'],'emp'=>$aConfig['emp_quartup']));
        if (substr($validacion,0,2)=="ok") {
            $this->validate = true;
        }

        return $validacion;
    }

    /**
     * Llamada webservice, con 30 segundos de timeout por defecto
     *
     */
    private function call ($func, $aPar, $msTimeout=30000) {
        if ($this->validate) {
            // montamos parámetro de envio
            $par = is_array($aPar) ? base64_encode(serialize($aPar)) : $aPar;
            if ($this->swWS) {
                $strCall = $this->oClient->call($func,array('par'=>$par));
            } else {
                $strCall = $this->curlCall($func, array('para'=>$par), $msTimeout);
            }
            // para las funciones que retornan 'ok'
            if ($strCall=='ok') {
                return $strCall;
            }
            // si el retorno no está en base64, lo devolvemos sin conversión
            $str = base64_decode($strCall,true);
            $str = $str ? unserialize($str) : $str;
            return $str ? $str : $strCall;
        }
        return 'La sesión no está validada.';
    }

    /**
     * Llamada 'curl', con 30 segundos de timeout por defecto
     *
     */
    private function curlCall ($func, $aPar, $msTimeout=30000) {
        // añadimos datos de conexión
        $aPar['user'] = $this->aConfig['usr_quartup'];
        $aPar['pass'] = $this->aConfig['pass_quartup'];
        $aPar['empr'] = $this->aConfig['emp_quartup'];
        $aPar['swWS'] = 'false';
        $aPar['func'] = $func;

        // si recibimos un 'timeout' en el '$aPar', reescribimos el del parámetro (esto nos permite controlar el 'timeout' desde las llamadas a la clase)
        if (isset($aPar['msTimeout'])) {
            $msTimeout = $aPar['msTimeout'];
            //unset($aPar['msTimeout']);        // no lo hacemos para que nos llegue al servidor, a título informativo
        }

        // abrimos conexión
        //$this->oClient = curl_init($this->aConfig['url_srv'] . (strpos($this->aConfig['url_srv'],'?')===false ? '?' : ''). http_build_query($aPar));  // caso para enviar con GET
        if (! $this->oClient) {
            $this->oClient = curl_init($this->aConfig['url_srv']);
        }

        // POSIBLES OPTIMIZACIONES:
        //
        // CONTROL SSL
        // ENVIAR IP FIJA, PARA EVITAR DNS-LOOKUP
        // USAR curl_multi_* PARA EJECUTAR VARIAS LLAMADAS DE FORMA CONCURRENTE

        // configuramos
        curl_setopt($this->oClient, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->oClient, CURLOPT_HEADER, 0);
        curl_setopt($this->oClient, CURLOPT_POST, true);
        //curl_setopt($this->oClient, CURLOPT_USERAGENT, "Mozilla/4.0)");
        curl_setopt($this->oClient, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->oClient, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->oClient, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->oClient, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->oClient, CURLOPT_POSTFIELDS, $aPar);
        curl_setopt($this->oClient, CURLOPT_TIMEOUT_MS, $msTimeout);

        // llamamos
        $result = curl_exec($this->oClient);
        // para los casos de 'timeout' pequeño (el cliente se libera sin esperar respuesta), quitamos el error aparente
        if ($result===false && $msTimeout<=100) {
            $result = 'Sin retorno, por tener msTimeout='.$msTimeout;
        }

        //show information regarding the request
        //print_r(curl_getinfo($this->oClient));
        //echo curl_errno($this->oClient) . '-' . curl_error($this->oClient);

        // si no hay timeout, no cerramos, para reaprovechar el objeto y optimizar tiempo
        if ($msTimeout <= 100) {
            curl_close($this->oClient);
            $this->oClient = null;
        }

        return $result===false ? 'ERROR: fallo de conexión.' : $result;
    }

    /**
     * Retorna indicador true/false de si la conexión ha sido OK
     *
     */
    function getValidate(){
        return $this->validate;
    }

    /**
     * Cierra la sesion quartup
     *
     */
    function __destruct(){
        // no cerramos sesión para poder reutilizarla en siguientes llamadas
        //if ($this->validate)
        //    $this->oClient->call('logout',array());
    }

    /**
     * helloworld, prueba básica para probar la respuesta del server nusoap, no necesita iniciar sesion en quartup
     *
     */
    function hello(){
        return $this->call('hello', array('nom'=>'PRUEBA'));
    }

    /**
     * Printa los datos correspondientes en función del nivel de debug
     * @param array $resultado resultado de la llamada a la función nusoap
     * @return  null
     *
     */
    function showdebug($resultado=false){
        switch($this->debug) {
            case 1:
                if ($resultado) {
                    echo '<pre>';print_r($resultado);echo '</pre>';
                }
                break;
            case 2:
                echo '<h2>Request</h2>';
                echo '<pre>' . htmlspecialchars($this->oClient->request, ENT_QUOTES, 'ISO-8859-15') . '</pre>';
                echo '<h2>Response</h2>';
                echo '<pre>' . htmlspecialchars($this->oClient->response, ENT_QUOTES, 'ISO-8859-15') . '</pre>';
                break;
            case 3:
                echo '<h2>Debug</h2>';
                echo '<pre>' . htmlspecialchars($this->oClient->debug_str, ENT_QUOTES, 'ISO-8859-15') . '</pre>';
                break;
        }
    }

//***************************************************Webservices standards Quartup****************************************************

    /**
     * Creamos/modificamos cabecera/líneas de documento comercial
     * Esta función permite crear/modificar un documento comercial (sin dependencias de otros)
     *
     * @param aPar: 	Array asociativa con los campos obligatorios/opcionales de los registros:
     * 					'soporte'		    => tipo de documento a crear/modificar (cualquiera de los que admite el cialdoco)
     * 					'aCialdoco'         => array con los campos del cialdoco que se consideren necesarios
     * 					                       (no necesitan estar todos, pero lo habitual es que esté la serie, el año, la fecha, y el código de cuenta por lo menos)
     * 					'aaCialdocolin'     => array doble de los campos del cialdocolin que se consideren necesarios
     * 					                       (no necesitan estar todos, pero lo habitual es que esté el código de producto y la cantidad por lo menos)
     * 					'aCFCialdoco'       => array de campos del 'cialdoco' a los que aplicar el 'setChangefield'
     * 					                       (si no se recibe, se asume array('cod_cuenta'))
     * 					'aCFCialdocolin'    => array de campos del 'cialdoco' a los que aplicar el 'setChangefield'
     * 					                       (si no se recibe, se asume array('cod_articulo','cantidad'))
     * @return array con los id's de las lineas de pedido y albaran
     * 					'ko'		        => posible mensaje de error global de la transacción
     * 					'aKeyCialdoco'		=> array de campos de las claves principales del registro cialdoco creado
     * 					'koCialdoco'	    => posible mensaje de error sobre cabecera
     * 					'aaKeyCialdocolin'  => array de campos de las claves principales de los registros cialdocolin creados
     * 					'aKoCialdocolin'	=> array de posibles mensajes de error sobre líneas
     */
    function qu_updateCialdoco_c($aPar){
        return $this->call('qu_updateCialdoco_s',$aPar);
    }

    /**
     * Obtención de datos de productos a partir de una referencia y un cliente opcional.
     * Los parámetros de entrada son:
     *      array   (
     *                  'reference'     => $reference, (referencia a buscar, puede ser un 'string' o un array de 'strings', para buscar múltiples referencias en una sola petición)
     *                      // parámetros opcionales generales:
     *                  'id_customer'   => $id_customer, (se usa para calcular precios, pero podría usarse en un futuro para obtención de referencias personalizadas u otros)
     * 					'id_shop'       => $id_shop, (se usa si hay multienda configurada, y tenemos tarifas por tienda, pero podría usarse en un futuro para otros menesteres)
     * 					'get_data'      => $get_data, (concatenación de las letras: 'P'(prices) 'S'(stocks); si se omite se asume 'PS')
     *                      // parámetros opcionales para los cálculos de precios:
     *                  'price_date'    => $price_date, (se asume la fecha actual por defecto)
     *                  'price_quantity'=> $price_quantity, (se asume 1 por defecto)
     *                      // parámetros opcionales para los cálculos de stocks:
     *                  'stock_store'   => stock_store, (almacén a calcular, se asume 'DISPON' por defecto, que es el consolidado de todos los almacenes de disponibilidad)
     * 					'stock_date'    => $stock_date, (fecha a calcular stocks, si se omite se usa la fecha actual)
     * 					'stock_time'    => $stock_time  (hora a calcular stocks, si se omite se usa la hora actual)
     * 					'pending_date'  => $pending_date  (fecha a calcular los stocks pendientes, si se omite se usa la fecha actual más los días que estén configurados en la empresa)
     *              )
     * Se buscan todos los productos que encajen con esa referencia, a través de la referencia principal, el código de barras, y las referencias alternativas
     * Se retornan todos los productos encontrados, con la siguiente información:
     *      array   (
     *                  array(      // parámetros que se retornan siempre:
     *                          'type'                  => $type,                   // 'N'->Normal, encontrado por la referencia,  'S'->Sustitutivo de alguno de los normales
     *                          'reference'             => $reference,
     *                          'referenceSubstitute'   => $referenceSubstitute,    // Referencia del producto al que sustituye (solo para los $type=='S')
     *                          'description'           => $description,
     *                              // parámetros que se retornan si se piden precios
     *                          'priceTaxExc'           => $priceTaxExc,
     *                          'priceTaxInc'           => $priceTaxInc,
     *                          'discounts'             => $discounts,
     *                              // parámetros que se retornan si se piden stocks
     *                          'stock'                 => $stock,
     *                          'stockToSend'           => $stockToSend,
     *                          'stockToReceive'        => $stockToReceive,
     *                          'aaToReceive'           => array(array('stockToReceive'=>$stockToReceive, 'dateToReceive'=>$dateToReceive), ...),
     *                        ),
     *                  ...
     *              )
     *
     */
    function qu_getProductByReference_c($aPar) {
        return $this->call('qu_getProductByReference_s',$aPar);
    }

    /**
     * Obtención de datos de pedidos (orders) a partir de información diversa
     * Los parámetros de entrada son:
     *      array   (
     *                  // parámetros opcionales generales:
     *                  'id_shop'           => número de tienda (opcional, salvo si hay multitienda, o hay diferentes series para cada tienda)
     *                  'id_order'          => número de pedido (opcional, obligatorio si no hay 'id_customer')
     *                  'id_customer'       => cliente  (opcional, obligatorio si no hay 'id_order')
     *                  'date_from'         => fecha desde
     *                  'date_to'           => fecha hasta
     *                  'sw_only_pending'   => indicador de solo pedidos pendientes (1-true, 0-false[default])
     *                  'sw_detail'         => indicador de retorno de detalle (1-true, 0-false[default])
     *              )
     * Se buscan todos los pedidos que encajen con los filtros
     * Se retorna la siguiente información:
     *      array   (
     *                  array(
     *                          'cod_serie'             => serie,
     *                          'number'                => nómero pedido,
     *                          'out_reference'         => su referencia
     *                          'date'                  => fecha pedido,
     *                          'id_customer'           => ID de cliente,
     *                          'sw_state'              => 'P'->pendiente, 'T'->traspasado, 'D'->detenido,
     *                          'cod_detention'         => código de detención (solo para los casos sw_state='D'),
     *                          // array de líneas (solo cuando se activa 'sw_detail')
     *                          'aaLines' => array(
     *                                          array(
     *                                              'id_product'        => ID producto,
     *                                              'reference_product' => referencia producto,
     *                                              'description'       => descripción producto,
     *                                              'date_delivery'     => fecha entrega teórica,
     *                                              'quantity'          => cantidad pedido,
     *                                              'quantity_pending'  => cantidad pendiente,
     *                                              'price'             => precio unitario,
     *                                              'sw_state'          => 'P'->pendiente, 'T'->traspasado, 'D'->detenido,
     *                                              'cod_detention'     => código de detención (solo para los casos sw_state='D'),
     *                                              // array de líneas de trazabilidad de la línea
     *                                              'aaTrace' => array(
     *                                                              array(
     *                                                                  'type_doc'      => tipo documento ('AV'->albaran, 'FV'->factura),
     *                                                                  'cod_serie'     => serie,
     *                                                                  'date'          => fecha,
     *                                                                  'quantity'      => cantidad traspasada,
     *                                                                  ),
     *                                                              ),
     *                                              ),
     *                                          ),
     *                                      ...
     *                                      ),
     *                          ...
     *                )
     *
     */
    function qu_getOrders_c($aPar) {
        return $this->call('qu_getOrders_s',$aPar);
    }

    /**
     * Fusión de las dos llamadas de stocks y precios, por optimización (solo se usa cuando los parámetros de las dos funciones son los mismos)
     * Falta activar control de cache (para evitar llamadas repetitivas) y activación de 'swAll' cuando se pide un 'ia' ó 'ca'
     */
    function qu_selectAlmastocPrecios_c($aPar) {
        return $this->call('qu_selectAlmastocPrecios_s',$aPar);
    }

    /**
     * Obtenemos stocks de una lista de almacenes/productos
     *
     * @param aPar: 	Array asociativa con los campos obligatorios/opcionales de los registros:
     * 					'id_shop'			=> Id opcional de la 'shop' de prestashop en curso
     * 					'aAlma'				=> array opcional de almacenes a testar (si se omite se usará el consolidado de los almacenes disponibles)
     * 					'aArti'      		=> array identificadores de productos/combinaciones a testar (obligatoria), con el formato:
     * 											array( 'caxxx1', 'caxxx2', 'iayyy1', 'iayyy2', 'iczzz1', 'iczzz2', ...)
     * 											donde los dos primeros dígitos de cada valor equivalen a: 'ca'->codigo-articulo, 'ia'->id-articulo, 'ic'->id-combinacion
     * 											de esta manera se pueden pedir tantos articulos y/o combinaciones como se quieran, de una sola vez
     * 					'fechaStock'        => fecha opcional a calcular los stocks, si se omite se asigna la fecha actual
     * 					'horaStock'         => hora opcional a calcular los stocks, si se omite se asigna la hora actual
     * 					'fechaPdtes'        => fecha opcional a calcular los stocks pendientes, si se omite se asigna la fecha actual
     * @return array múltiple con los datos pedidos
     * 					array['almacen']['articulo']['tipo'] ...
     * 					donde el 'articulo' se retorna en el mismo formato que se recibe 'caxxx', 'iayyy', 'iczzz', ...
     * 					donde el 'tipo' tiene los valores:
     * 						'st'	-> stock,
     * 						'pe'	-> pdte.entrar,
     * 						'ps'	-> pdte.salir,
     * 						'aPe' 	-> array de las fechas/cantidades pendientes de entrar:    array( array('fecha1', 'cantidad1'), array('fecha2', 'cantidad2'), ... )
     */
    function qu_selectAlmastoc_c($aPar) {
        // si recibimos un solo producto/combinación, controlamos 'cache'
        if (count($aPar)==1 && count($aPar['aArti'])==1) {
            // buscamos en el 'cache', y si está retornamos sin llamar al servidor
            $arti = $aPar['aArti'][0];
            if (isset(self::$aaCache['qu_selectAlmastoc_c'])) {
                foreach (self::$aaCache['qu_selectAlmastoc_c'] as $aRet) {
                    if (isset($aRet['DISPON'][$arti])) {
                        return $aRet;
                    }
                }
            }
            // en los casos ca/ia, activamos formato 'all', para forzar calcular todas las combinaciones
            $tt = substr($arti,0,2);
            if ($tt=='ca' || $tt=='ia') {
                $aPar['swAllCombi'] = true;
            }
        }
        $aRet = $this->call('qu_selectAlmastoc_s',$aPar);
        self::$aaCache['qu_selectAlmastoc_c'][] = $aRet;
        return $aRet;
    }

    /**
     * Buscamos el precio del articulo para los parámetros indicados
     *
     * @param aPar: 	Array asociativa con los campos obligatorios/opcionales de los registros:
     * 					'id_shop'			=> identificador de tienda opcional (solo se usa en las llamadas desde Prestashop)
     * 					'id_customer'		=> identificador de cuenta opcional (solo se usa en las llamadas desde Prestashop, donde se usa el 'id')
     * 					'cod_cuenta'		=> código de cuenta opcional (solo se usa en las llamadas desde Quartup)
     * 					'cod_tarifa'		=> código de tarifa opcional (solo se usa en las llamadas desde Quartup)
     * 					'cod_moneda'		=> código de moneda opcional (solo se usa en las llamadas desde Quartup), si no se recibe, se asume 'EUR'
     * 					'fecha'				=> fecha de tarifa opcional (solo se usa en las llamadas desde Quartup), si no se recibe, se asume la fecha del día
     * 					'cantidad'			=> cantidad de unidades, opcional (debido a que los precios pueden ser escalados), si no se recibe, se asume "1"
     * 					'aArti'      		=> array identificadores de productos/combinaciones a testar (obligatoria), con el formato:
     * 											array( 'caxxx1', 'caxxx2', 'iayyy1', 'iayyy2', 'iczzz1', 'iczzz2', ...)
     * 											donde los dos primeros dígitos de cada valor equivalen a: 'ca'->codigo-articulo, 'ia'->id-articulo, 'ic'->id-combinacion
     * 											de esta manera se pueden pedir tantos articulos y/o combinaciones como se quieran, de una sola vez
     * @return array múltiple con los datos pedidos
     * 					array['articulo']['tipo'] ...
     * 					donde el 'articulo' se retorna en el mismo formato que se recibe 'caxxx', 'iayyy', 'iczzz', ...
     * 					donde el 'tipo' tiene los valores:
     * 						'precio_mone'	-> precio-sin-iva,
     * 						'preivi_mone'	-> precio-iva-incluido,
     * 						'descuentos'	-> %descuento,
     * 						'tpc_iva'		-> %iva,
     * 						'tpc_rec'		-> $rec,
     */
    function qu_selectPrecios_c($aPar){
        // array de articulos para enviar al webservice
        $aArtiWS = array();
        // array de retorno
        $aRet = array();

        // testamos todo lo pedido
        foreach ($aPar['aArti'] as $q=>$arti) {
            // si está en 'cache', lo guardamos en el array de retorno directamente
            if (isset(self::$aaCache['qu_selectPrecios_c'])) {
                foreach (self::$aaCache['qu_selectPrecios_c'] as $aCache) {
                    if (isset($aCache[$arti])) {
                        $aRet[$arti] = $aCache[$arti];
                        continue;
                    }
                }
            }
            // como no está en caché, lo guardamos para llamar al WS
            $aArtiWS[] = $arti;
            // en los casos ca/ia, activamos formato 'all', para forzar calcular todas las combinaciones
            $tt = substr($arti,0,2);
            if ($tt=='ca' || $tt=='ia') {
                $aPar['swAllCombi'] = true;
            }
        }

        // si recibimos un solo producto/combinación, controlamos 'cache'
        /*if (count($aPar)==1 && count($aPar['aArti'])==1) {
            // buscamos en el 'cache', y si está retornamos sin llamar al servidor
            $arti = $aPar['aArti'][0];
            if (isset(self::$aaCache['qu_selectPrecios_c'])) {
                foreach (self::$aaCache['qu_selectPrecios_c'] as $aCache) {
                    if (isset($aCache[$arti])) {
                        return $aCache;
                    }
                }
            }
            // en los casos ca/ia, activamos formato 'all', para forzar calcular todas las combinaciones
            $tt = substr($arti,0,2);
            if ($tt=='ca' || $tt=='ia') {
                $aPar['swAllCombi'] = true;
            }
        }*/

        // WS
        if ($aArtiWS) {
            $aPar['aArti'] = $aArtiWS;
            $aRetWS = $this->call('qu_selectPrecios_s',$aPar);
            self::$aaCache['qu_selectPrecios_c'][] = $aRetWS;
            // añadimos lo leído por WS al array de retorno
            foreach ($aRetWS as $arti=>$a) {
                $aRet[$arti] = $a;
            }
        }

        return $aRet;
    }

    /**
     * Creamos registro de venta de Haribo
     *
     * @param aPar: 	Array asociativa con los campos obligatorios/opcionales de los registros:
     * 					'id_quemtpvs'	    => id de tpv
     * 					'peso'      		=> peso
     * 					'precio'      		=> precio
     * 					'importe'      		=> importe
     * 					'fecha'             => fecha
     * 					'hora'              => hora
     * @return array('ko'=>$ko) de posible mensaje de error, o string vacía si ha sido todo OK
     */
    function qu_updateHarivent_c($aPar) {
        $aRet = $this->call('qu_updateHarivent_s',$aPar);
        return $aRet;
    }

//************************************ Funciones administrativas de Quartup ***************************************************

    /**
     * Creamos una nueva instalación, solo permitido si el usuario de conexión es un super-admin
     * Retorna un array asociativa con lo creado:
     *          		'cod_empresa'		=> código de la empresa de Quartup
     *          		'cod_usuario_ws'	=> código de usuario para los WS de PS (solo si se reciben los 'url_ws_ps' y 'key_ws_ps')
     *          		'pas_usuario_ws'	=> password de usuario para los WS de PS (solo si se reciben los 'url_ws_ps' y 'key_ws_ps')
     *          		'error'				=> posible mensaje de error (si no se producen errores se retorna string vacía)
     *
     * @param $aPar: 	Array asociativa con los parámetros requeridos:
     *          		'name'		=> nombre de la instalación/empresa
     *          		'email'		=> e-mail de identificación para el usuario principal de uso de la instalación
     *          		'password'	=> clave de paso de Quartup para el usuario principal de uso de la instalación
     *          		'url_ws_ps'	=> URL de acceso a los WS de PS, para las instalaciones que se conecten a un PS
     *          		'key_ws_ps'	=> key de acceso a los WS de PS, para las instalaciones que se conecten a un PS
     * @return array
     */
    function qa_createInstallation_c($aPar){
        return $this->call('qa_createInstallation_s',$aPar);
    }

//***************************************************Webservices PrestaShop****************************************************

    /**
     * Creamos/modificamos cabecera/línea de pedido de venta del ERP, a partir de los datos 'xml' del pedido de prestashop
     *
     * @param aPar: 	Array asociativa con dos posibles formatos:
     *      formato 1: el array solo contiene un campo con el 'id' de la 'order' (caso simplificado)
     *          'id'                => número de 'id' de la 'order'
     *      formato 2: el array contiene los string 'xml' de cada tabla implicada en el pedido:
     * 			'orders'            => xml de <orders> (opcional), pueden venir tantos pedidos como se quieran, pero habitualmente hay uno solo
     * 			'order_details'	    => xml de <order_details> (opcional), han de venir todas las líneas de los pedidos del 'orders'
     * 			'customer_threads'	=> xml de <customer_threads> (opcional pero recomendable)
     * 			'customer_messages'	=> xml de <customer_messages> (opcional pero recomendable)
     * 			'customers'	        => xml de <customers> (opcional pero recomendable), pueden venir tantos clientes como se quieran, pero habitualmente hay uno solo
     * 			'address_delivery'  => xml de <address_delivery> (opcional pero recomendable), pueden venir tantas direcciones como se quieran, pero habitualmente hay una sola
     * 			'address_invoice'   => xml de <address_invoice> (opcional pero recomendable), pueden venir tantas direcciones como se quieran, pero habitualmente hay una sola
     * 			'addresses'         => xml de <addresses> (deprecated), se usa en caso de no recibir alguno de los dos anteriores, en el futuro se obsoleteará
     * 			'products'	        => xml de <products> (opcional pero recomendable), habitualmente hay tantos productos como líneas en el pedido
     * @return array con los posibles mensajes de error de grabación:
     * 			'errorMaescuen'	    => posible mensaje de error sobre maestro de cuentas
     * 			'errorMaesagen'	    => posible mensaje de error sobre maestro de transportistas
     * 			'errorMaesarti'	    => posible mensaje de error sobre maestro de productos
     * 			'errorCialdoco'	    => posible mensaje de error sobre cabecera
     * 			'errorCialmess'	    => posible mensaje de error sobre hilo de mensaje
     * 			'errorCialmesslin'	=> posible mensaje de error sobre línea de mensaje
     */
    function ps_updateOrder_c($aPar){
        return $this->call('ps_updateOrder_s',$aPar);
    }

    /**
     * Actualizamos registros de ficheros maestros
     *
     * @param aPar: 	Array asociativa con los datos:
     * 		'iud'	                => Indicador de tipo transacción sql: 'i'->insert, 'u'->update, 'd'->delete (si no se recibe se asume 'i'/'u' automático)
     * 		'products'	            => Opcional, String con el Xml del registros 'products'
     * 		   'id_product'	        => Opcional, Id del 'product' cuando el 'iud' es 'd' de baja
     * 		'combinations'	        => Opcional, String con el Xml del registros 'combinations'
     * 		   'id_product_combination' => Opcional, Id del 'product' cuando el 'iud' es 'd' de baja (se refiere a baja de todas la combinaciones del producto)
     * 		   'id_combination'         => Opcional, Id del 'combination' cuando el 'iud' es 'd' de baja
     * 		'categories'	        => Opcional, String con el Xml del registros 'categories'
     * 		'customers'	            => Opcional, String con el Xml del registros 'customers'
     * 		   'id_customer'        	=> Opcional, Id del 'customer' cuando el 'iud' es 'd' de baja
     * 		'customer_threads'	    => Opcional, String con el Xml del registros 'customer_threads'
     * 		   'id_customer_thread'     => Opcional, Id del 'customer_thread' cuando el 'iud' es 'd' de baja
     * 		'customer_messages'	    => Opcional, String con el Xml del registros 'customer_messages'
     * 		   'id_customer_message'    => Opcional, Id del 'customer_message' cuando el 'iud' es 'd' de baja
     * 		'carriers'	            => Opcional, String con el Xml del registros 'carriers'
     * @return array con los posibles mensajes de error de grabación:
     * 		'errorMaesarti'	        => posible mensaje de error sobre maestro de productos
     * 		'errorMaescombart'	    => posible mensaje de error sobre maestro de combinaciones
     * 		'errorMaesarti2'	    => posible mensaje de error sobre maestro de familias
     * 		'errorMaescuen'	        => posible mensaje de error sobre maestro de cuentas
     * 		'errorCialmess'	        => posible mensaje de error sobre maestro de hilos de mensajes
     * 		'errorCialmesslin'	    => posible mensaje de error sobre maestro de líneas de mensajes
     * 		'errorMaesagen'	        => posible mensaje de error sobre maestro de transportistas
     */
    function ps_updateMaestable_c($aPar){
        return $this->call('ps_updateMaestable_s',$aPar);
    }

    /**
     * Retorna registros de servicios activos junto con sus contratos activos
     *
     * @param aPar: 	Array asociativa con los campos obligatorios/opcionales de los registros:
     * 					'cod_fami_servicio' => código de familia de servicios a obtener, opcional, si se omite se retornarán todas
     * @return array de doble índice, con el primer índice numérico (0,1,2...) para cada registro de servicio obtenido,
     *                  y el segundo índice con los códigos de campos del registro
     * 					'cod_servicio'              => código del servicio
     * 					'nombre'	                => nombre del servicio
     * 					'cod_fami_servicio'         => código de la familia
     * 					'Rcod_fami_servicio_nombre'	=> nombre de la familia
     * 					'Rcontratos_contrato'       => número de contrato sobre el servicio, si no hay estará a cero
     */
    function ps_selectSeteserv_c($aPar=array()){
        return $this->call('ps_selectSeteserv_s',$aPar);
    }

//***************************************************Webservices Magento****************************************************

    /**
     * Creamos/modificamos pedido de venta del ERP, a partir del id del pedido de magento
     *
     * @param id: 		Id del pedido de maagento
     * @return string	Mensaje de respuesta: 'ok'->todo correcto, !='ok'->texto del error ocurrido
     */
    function mg_updateOrder_c($id){
        return $this->call('mg_updateOrder_s',$id);
    }

}