{*
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
/*$('document').ready(function()
{
	$('img[rel^=ajax_id_quartupsearcher_]').click(function()
	{
		var ids =  $(this).attr('rel').replace('ajax_id_quartupsearcher_', '');
		ids = ids.split('_');
		var id_product_mail_alert = ids[0];
		var id_product_attribute_mail_alert = ids[1];
		var parent = $(this).parent().parent();

		$.ajax({
			url: "{$link->getModuleLink('quartupsearcher', 'actions', ['process' => 'remove'])|addslashes}",
			type: "POST",
			data: {
				'id_product': id_product_mail_alert,
				'id_product_attribute': id_product_attribute_mail_alert
			},
			success: function(result)
			{
				if (result == '0')
				{
					parent.fadeOut("normal", function()
					{
						parent.remove();
					});
				}
 		 	}
		});
	});
});*/
</script>

{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='Manage my account' mod='quartupsearcher'}" rel="nofollow">{l s='My account' mod='quartupsearcher'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Histórico avanzado' mod='quartupsearcher'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div id="quartupsearcher_block_account">
	<h2>{l s='Mi histórico avanzado' mod='quartupsearcher'}</h2>
	{if $quartupsearcher_orders}
		<div>
			{foreach from=$quartupsearcher_orders item=qorder}
			<div class="quartupsearcher clearfix">
				<!--<a href="{$link->getProductLink($qorder.id_product, null, null, null, null, $qorder.id_shop)}" title="{$qorder.name|escape:'html':'UTF-8'}" class="product_img_link"><img src="{$link->getImageLink($qorder.link_rewrite, $qorder.cover, 'small_default')|escape:'html'}" alt=""/></a>
				<h3><a href="{$link->getProductLink($qorder.id_product, null, null, null, null, $qorder.id_shop)|escape:'html'}" title="{$qorder.name|escape:'html':'UTF-8'}">{$qorder.name|escape:'html':'UTF-8'}</a></h3>
				<div class="product_desc">{$qorder.attributes_small|escape:'html':'UTF-8'}</div>

				<div class="remove">
					<img rel="ajax_id_quartupsearcher_{$qorder.id_product|intval}_{$qorder.id_product_attribute|intval}" src="{$img_dir}icon/delete.gif" alt="{l s='Remove' mod='quartupsearcher'}" class="icon" />
				</div>-->
			</div>
			{/foreach}
		</div>
	{else}
		<p class="warning">{l s='No hay pedidos a mostrar.' mod='quartupsearcher'}</p>
	{/if}

	<ul class="footer_links">
		<li class="fleft"><a href="{$link->getPageLink('my-account', true)}" rel="nofollow"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /></a><a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='Back to Your Account' mod='quartupsearcher'}">{l s='Back to Your Account' mod='quartupsearcher'}</a></li>
	</ul>
</div>