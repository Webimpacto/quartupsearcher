<?php

require 'QU_XwsClient_Search.php';

function executeWS ($oClient, $method, $aPar=array()) {
    $mtI0 = microtime(true);
    $aRet = call_user_func_array(array($oClient, $method), array($aPar));
    echo "<pre>$method:\n";
    echo "Input parameters:\n";
    print_r($aPar);
    echo "Output parameters:\n";
    print_r($aRet);
    echo '</pre>';
    echo "<b>Elapsed time: ".(microtime(true)-$mtI0)."</b><br>\n";
}

// parámetros de conexión
$aConfig = array();
//$aConfig['url_srv']     = "http://localhost/dev/quptmp/quartup/trunk2/b_webserv/QU_XwsServer.php?wsdl";     // para pruebas locales
$aConfig['url_srv']     = "https://erp.quartup.net/b_webserv/QU_XwsServer.php?wsdl";         // para pruebas con la empresa demo del servidor real
//$aConfig['usr_quartup'] = 'u-0002-ws';
//$aConfig['pass_quartup']= 'u-0002-ws6466';
//$aConfig['emp_quartup'] = '0001';

$aConfig['usr_quartup'] = 'i-0090-ws';
$aConfig['pass_quartup']= 'EXP386388-ws';
$aConfig['emp_quartup'] = '0091';
$swWS = false;       // poner a 'false' si no queremos usar 'nusoap' y usar llamadas 'curl' directas, que mejoran el rendimiento

// control tiempo total
$mtI = microtime(true);

// creamos objeto
$mtI0 = microtime(true);
$oClient = new QU_XwsClient_Search(0, $aConfig, $swWS);
echo $oClient->getValidate() ? "Validated session.<br>\n" : "NOT VALIDATED SESSION !<br>\n";
echo "<b>Elapsed time constructor: ".(microtime(true)-$mtI0)."</b><br>\n";

// llamada a 'hello'
executeWS($oClient, 'hello');

// llamada a 'qu_getProductByReference_s'
$aPar = array();
//$aPar['reference'] = '01010m';
//$aPar['pending_date'] = '99991231';
$aPar['reference'] = $_GET['search'] ? $_GET['search'] : 'CE247A';
executeWS($oClient, 'qu_getProductByReference_c', $aPar);

// tiempo total
echo "<br><b>Elapsed total time: ".(microtime(true)-$mtI)."</b><br>\n";
