<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// Include Module
include_once(dirname(__FILE__).'/../../quartupsearcher.php');

class QuartupsearcherDefaultModuleFrontController extends ModuleFrontController
{

	/**
	 * Initialize search controller
	 * @see FrontController::init()
	 */
	public function init()
	{

		parent::init();
		//$this->instant_search = Tools::getValue('instantSearch');

		/*$this->ajax_search = Tools::getValue('ajaxSearch');

		if ($this->instant_search || $this->ajax_search) {
			$this->display_header = false;
			$this->display_footer = false;
		}*/
	}

	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{

		//Only controller content initialization when the user use the normal search
		parent::initContent();

		if (((bool)Tools::isSubmit('quartupsearcher_button')) == true || ((bool)Tools::isSubmit('submit_quartupsearcher_top')) == true) {
			$search = Tools::getValue('quartupsearch_query');

			if(!empty($search)) {

				$aPar = array();
				//$aPar['reference'] = 'CE247A';
				$aPar['reference'] = $search;
				if(!empty($this->context->customer) && $this->context->customer->isLogged())
					$aPar['id_customer'] = (int)$this->context->customer->id;
				$aPar['pending_date'] = '99991231';
				//ddd($aPar);
				$module = Module::getInstanceByName('quartupsearcher');
				$data = $module->makeSearch($aPar, false);
				//$data = $module->testSearch();

				$product_searcher = $data['aRet'];
				$products = array();
				if(!empty($product_searcher)) {
					foreach ($product_searcher as $product) {
						if ($product['type'] == 'N') {
							$products['N'][] = $product;
						} elseif ($product['type'] == 'E') {
							$products['E'][] = $product;
						}
					}
				}

				//ddd($products);
				$this->context->smarty->assign(
						array(
								'quartupsearch_query' => $search,
								'product_searcher' => $products
						)
				);
			}else{
				$this->context->smarty->assign(
						array(
								'quartupsearch_query' => $search,
								'product_searcher' => false
						)
				);
			}


		}


		//ddd($this->context->getModuleLink('Quartupsearcher', 'Default'));
		$this->setTemplate('quartupsearcher.tpl');
	}


	public function setMedia()
	{
		parent::setMedia();

		$this->context->controller->addCSS(_MODULE_DIR_.'quartupsearcher/views/css/front.css');
		$this->context->controller->addJS(_MODULE_DIR_.'quartupsearcher/views/js/front.js');
		/*if (!$this->instant_search && !$this->ajax_search) {
			$this->addCSS(_THEME_CSS_DIR_.'product_list.css');
		}*/
	}
}
