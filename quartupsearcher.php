<?php
/**
 * 2009-2016 Webimpacto
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@webimpacto.es so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Webimpacto to newer
 * versions in the future. If you wish to customize Webimpacto for your
 * needs please refer to http://www.webimpacto.es for more information.
 *
 *  @author    Webimpacto Consulting S.L. <info@webimpacto.es>
 *  @copyright 2009-2016 Webimpacto Consulting S.L.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Webimpacto Consulting S.L.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/api/QU_XwsClient.php');

class Quartupsearcher extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'quartupsearcher';
        $this->tab = 'search_filter';
        $this->version = '1.0.0';
        $this->author = 'Webimpacto';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Módulo de búsqueda avanzada Quartup');
        $this->description = $this->l('Muestra una tabla de resultados extraidos directamente del ERP Quartup a través de la refencia');

        $this->confirmUninstall = $this->l('¿Estás seguro de desinstalar el módulo? Una vez realizado, no podrás hacer búsquedas de referencias contra el ERP');
    }

    public function install()
    {
        Configuration::updateValue('QUSEARCHER_NUSOAP', false);

        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayMobileTopSiteMap') &&
        $this->registerHook('displayNav');
    }

    public function uninstall()
    {
        Configuration::deleteByName('QUSEARCHER_NUSOAP');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $style15 = '<style>
                label[for="active_on"],label[for="active_off"]{
                    float: none
                }
                </style>';
        if (((bool)Tools::isSubmit('submitQuartupsearcherModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        //$output.= $this->renderForm();
        if (_PS_VERSION_ < 1.6) {
            $output .= $style15;
        }
        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitQuartupsearcherModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Modo Debug'),
                        'name' => 'QUSEARCHER_DEBUG',
                        'is_bool' => true,
                        'desc' => $this->l('Muestra información extra para depurar los tiempos de respuesta'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activado')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Desactivado')
                            )
                        ),
                    ),
                    array(
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Usar Nusoap'),
                        'name' => 'QUSEARCHER_NUSOAP',
                        'is_bool' => true,
                        'desc' => $this->l('Más compatibilidad, menos rendimiento. Desactivado usa CURL'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activado')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Desactivado')
                            )
                        ),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'desc' => $this->l('La URL donde está la WSDL del ERP publicada. Ojo con el protocolo (http/s)'),
                        'name' => 'QUSEARCHER_URL_SRV',
                        'label' => $this->l('Quartup WSDL URL'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('El usuario para acceder a la API'),
                        'name' => 'QUSEARCHER_USER_SRV',
                        'label' => $this->l('Usuario Quartup'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Identificador de la empresa para acceder a la API'),
                        'name' => 'QUSEARCHER_EMP_SRV',
                        'label' => $this->l('Empresa Quartup'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'QUSEARCHER_PASS',
                        'label' => $this->l('Contraseña Quartup'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'QUSEARCHER_NUSOAP' => Configuration::get('QUSEARCHER_NUSOAP', null),
            'QUSEARCHER_DEBUG' => Configuration::get('QUSEARCHER_DEBUG', null),
            'QUSEARCHER_URL_SRV' => Configuration::get('QUSEARCHER_URL_SRV', null),
            'QUSEARCHER_PASS' => Configuration::get('QUSEARCHER_PASS', null),
            'QUSEARCHER_EMP_SRV' => Configuration::get('QUSEARCHER_EMP_SRV', null),
            'QUSEARCHER_USER_SRV' => Configuration::get('QUSEARCHER_USER_SRV', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayHeader()
    {
        return $this->hookHeader();
    }

    public function hookDisplayMobileTopSiteMap()
    {
        /* Place your code here. */
    }

    public function hookDisplayNav()
    {
        /* Place your code here. */
    }

    public function hookTop($params)
    {
        $key = $this->getCacheId('quartupsearcher-top'.((!isset($params['hook_mobile']) || !$params['hook_mobile']) ? '' : '-hook_mobile'));
        if (Tools::getValue('quartupsearch_query') || !$this->isCached('quartupsearcher-top.tpl', $key))
        {
            $this->smarty->assign(array(
                    //'blocksearch_type' => 'top',
                    'quartupsearch_query' => (string)Tools::getValue('quartupsearch_query')
                )
            );
        }
        //Media::addJsDef(array('quartupsearcher' => 'top'));
        return $this->display(__FILE__, 'quartupsearcher-top.tpl', Tools::getValue('quartupsearch_query') ? null : $key);
    }

    public function testSearch(){
        $aPar = array();
        $aPar['reference'] = 'CE247A';
        $aPar['pending_date'] = '99991231';
        $this->makeSearch($aPar,true);
    }


    /**
     * Función para realizar una búsqueda usando el método qu_getProductByReference_c
     * @method qu_getProductByReference_c
     * @param array $data Array de datos a buscar
     * @param boolean $debug Si queremos que se muestre la información debug
     * @return array Resultado de búsqueda en caso correcto
     */
    public function makeSearch($data,$debug=false){

        $debug  = Configuration::get('QUSEARCHER_DEBUG', null, false);
        $client = $this->startQuartupClient($debug);
        //p($debug);
        //p($client);
        return $this->executeWS($client, 'qu_getProductByReference_c', $data, $debug);
    }

    /**
     * Inicializa el cliente Quartup con las variables seteadas en la configuración del módulo
     * @param boolean $debug Si queremos que se muestre la información debug
     * @return boolean|\QU_XwsClient false en caso fallido, cliente Quartup si todo fue bien
     */
    public function startQuartupClient($debug=false){
        if($debug){
            $mtI0 = microtime(true);
        }
        $aConfig = array();
        $aConfig['url_srv']     = Configuration::get('QUSEARCHER_URL_SRV', null);
        $aConfig['usr_quartup'] = Configuration::get('QUSEARCHER_USER_SRV', null);
        $aConfig['pass_quartup']= Configuration::get('QUSEARCHER_PASS', null);
        $aConfig['emp_quartup'] = Configuration::get('QUSEARCHER_EMP_SRV', null);
        $swWS = Configuration::get('QUSEARCHER_NUSOAP', null);

        $quartupClient = new QU_XwsClient(0, $aConfig, $swWS);
        if($quartupClient->getValidate()){
            if($debug){
                echo "<b>Elapsed time constructor: ".(microtime(true)-$mtI0)."</b><br>\n";
            }
            return $quartupClient;
        }
        return false;
    }

    /**
     * Ejecuta la función data en "method" contra la API
     * @param QU_XwsClient $oClient Cliente Quartup
     * @param string $method Nombre del método a lanzar contra la WSDL
     * @param array $aPar Array de datos para pasar a la API WSDL
     * @param boolean $debug Si queremos que se muestre la información debug
     * @return array Resultado
     */
    public function executeWS ($oClient, $method, $aPar=array(),$debug=false) {
        if($debug){
            $mtI0 = microtime(true);
        }
        $aRet = call_user_func_array(array($oClient, $method), array($aPar));
        $result = array(
            'aPar' => $aPar,
            'aRet' => $aRet,
        );
        if($debug){
            echo "<pre>$method:\n";
            echo "Input parameters:\n";
            print_r($aPar);
            echo "Output parameters:\n";
            print_r($aRet);
            echo '</pre>';
            echo "<b>Elapsed time: ".(microtime(true)-$mtI0)."</b><br>\n";
        }
        return $result;
    }
}
