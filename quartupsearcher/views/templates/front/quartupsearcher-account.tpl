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
<style type="text/css">
	table#order-list tr.details td{
		padding: 9px 8px 11px 8px;
	}
</style>


{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='Manage my account' mod='quartupsearcher'}" rel="nofollow">{l s='My account' mod='quartupsearcher'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Histórico avanzado' mod='quartupsearcher'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div id="quartupsearcher_block_account">
	<h1 class="page-heading bottom-indent">{l s='Mi histórico avanzado' mod='quartupsearcher'}</h1>

	<div class="block-center" id="block-history">
		{if $quartupsearcher_orders && count($quartupsearcher_orders)}
			<table id="order-list" class="table table-bordered footab">
				<thead>
				<tr>
					<th class="first_item">{l s='Pedido' mod='quartupsearcher'}</th>
					<th class="item">{l s='S/Ref' mod='quartupsearcher'}</th>
					<th data-hide="phone" class="item">{l s='Referencia' mod='quartupsearcher'}</th>
					<th class="item">{l s='Fecha ' mod='quartupsearcher'}</th>
					<th class="item">{l s='Estado  ' mod='quartupsearcher'}</th>
					<th data-sort-ignore="true" data-hide="phone,tablet" class="last_item">&nbsp;</th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$quartupsearcher_orders.aRet item=qorder}
					{assign var="id_order" value=$qorder.number}
					<div class="quartupsearcher clearfix">
						<tr id="order-{$id_order|intval}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
							<td class="history_link bold">
								<a class="color-myaccount" href="javascript:showOrderDown({$id_order|intval});">
									{$qorder.number}
								</a>
							</td>
							<td>
								{$qorder.cod_serie|escape:'html':'UTF-8'}
							</td>
							<td>
								{$qorder.out_reference|escape:'html':'UTF-8'}
							</td>
							<td class="history_date bold">
								{if (!empty($qorder.date))}
									{assign var="fecha" value=DateTime::createFromFormat('Ymd', $qorder.date)}
									{$fecha->format('d/m/Y')}
								{/if}
							</td>
							<td class="history_state">
								{if ($qorder.sw_state == 'P')}
									{l s='Pendiente' mod='quartupsearcher'}
								{elseif $qorder.sw_state == 'T'}
									{l s='Traspasado' mod='quartupsearcher'}
								{elseif $qorder.sw_state == 'D'}
									{l s='Detenido' mod='quartupsearcher'} - {$qorder.cod_detention|escape:'html':'UTF-8'}
								{/if}
							</td>
							<td class="history_detail">
								<a class="btn btn-default button button-small" href="javascript:showOrderDown({$id_order|intval});">
								<span>
									{l s='Details'}<i class="icon-chevron-right right"></i>
								</span>
								</a>
								{if isset($opc) && $opc}
								<a class="link-button" href="{$link->getPageLink('order-opc', true, NULL, "submitReorder&id_order={$id_order|intval}")|escape:'html':'UTF-8'}" title="{l s='Reorder'}">
									{else}
									<a class="link-button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$id_order|intval}")|escape:'html':'UTF-8'}" title="{l s='Reorder'}">
										{/if}

										<i class="icon-refresh"></i>{l s='Reorder'}

									</a>
							</td>
						</tr>
						<tr style="display: none;" id="details-{$id_order|intval}" class="details">
							<td colspan="6">
								{if $qorder.aaLines && count($qorder.aaLines)}
									<table id="order-detail-list" class="table">
										<thead>
										<tr>
											<th class="first_item">{l s='Código ' mod='quartupsearcher'}</th>
											<th class="item">{l s='Artículo' mod='quartupsearcher'}</th>
											<th class="item">{l s='Cant. Pedida ' mod='quartupsearcher'}</th>
											<th class="item">{l s='Cant. Pdte ' mod='quartupsearcher'}</th>
											<th class="item">{l s='Precio/u.' mod='quartupsearcher'}</th>
											<th class="item">{l s='Precio Base ' mod='quartupsearcher'}</th>
											<th class="item">{l s='Estado ' mod='quartupsearcher'}</th>
											<th class="last_item">{l s='Fecha de entrega ' mod='quartupsearcher'}</th>
										</tr>
										</thead>
										<tbody>
										{foreach from=$qorder.aaLines item=qlines}
											<tr id="order-details-{$id_order|intval}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
												<td>{$qlines.reference_product|escape:'html':'UTF-8'}</td>
												<td>{$qlines.description|escape:'html':'UTF-8'}</td>
												<td>{$qlines.quantity|intval}</td>
												<td>{$qlines.quantity_pending|intval}</td>
												<td>{displayPrice price=$qlines.price}</td>
												<td>{displayPrice price=($qlines.price*$qlines.quantity)}</td>
												<td>
													{if ($qlines.sw_state == 'P')}
														{l s='Pendiente' mod='quartupsearcher'}
													{elseif $qlines.sw_state == 'T'}
														{l s='Traspasado' mod='quartupsearcher'}
													{elseif $qlines.sw_state == 'D'}
														{l s='Detenido' mod='quartupsearcher'} - {$qlines.cod_detention|escape:'html':'UTF-8'}
													{/if}
												</td>
												<td></td>
											</tr>
										{/foreach}
										</tbody>
									</table>
								{else}
									{l s='No hay detalles para el pedido '}{$id_order|intval}
								{/if}
							</td>
						</tr>
					</div>
				{/foreach}
				</tbody>
			</table>
			<div id="block-order-detail" class="unvisible">&nbsp;</div>
		{else}
			<p class="warning">{l s='No hay pedidos a mostrar.' mod='quartupsearcher'}</p>
		{/if}
	</div>

	<ul class="footer_links">
		<li class="fleft">
			<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
				<span>
					<i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='quartupsearcher'}
				</span>
			</a>
		</li>
	</ul>
</div>