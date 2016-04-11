<?php
class QuartupsearcherAccountModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();

		if (!Context::getContext()->customer->isLogged())
			Tools::redirect('index.php?controller=authentication&redirect=module&module=quartupsearcher&action=account');

		if (Context::getContext()->customer->id)
		{
                    if(Module::isInstalled('quartupsearcher') && Module::isEnabled('quartupsearcher')){
                        $quartupsearcher = Module::getInstanceByName('quartupsearcher');
                        $this->context->smarty->assign('id_customer', Context::getContext()->customer->id);
			$this->context->smarty->assign(
				'quartupsearcher_orders',
				$quartupsearcher->getAdvancedOrders((int)Context::getContext()->customer->id)
			);

			$this->setTemplate('quartupsearcher-account.tpl');
                    }
			
		}
	}
}