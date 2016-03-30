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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Block quartupsearcher module -->
<div id="quartupsearcher-block" class="block exclusive">
    <h4 class="title_block">{l s='Search for code' mod='quartupsearcher'}</h4>
    <form method="post" action="{$link->getModuleLink('quartupsearcher', 'default')|escape:'html'}" id="quartupsearcherbox">
        <p class="block_content">
            <label for="quartupsearcher_query_block">{l s='Búsqueda por código:' mod='quartupsearcher'}</label>
            <input class="quartupsearcher_query" type="text" id="quartupsearcher_query_block" name="quartupsearch_query" value="{if isset($quartupsearch_query) && $quartupsearch_query}{$quartupsearch_query|escape:'html':'UTF-8'|stripslashes}{/if}" />
            <input type="submit" name="quartupsearcher_button" id="quartupsearcher_button" class="btn btn-primary" value="{l s='Buscar' mod='quartupsearcher'}" />
        </p>
    </form>
</div>
<!-- /Block quartupsearcher module -->
<!-- Table quartupsearcher module -->

{if isset($product_searcher) && $product_searcher}
    <div class="quartupsearcher-table">
        {foreach $product_searcher as $key => $products}
            {if $key == 'E'}
                <div class="title-products"><span class="title-product-e">Productos equivalentes</span></div>
            {/if}
            <table id="quartupsearcher-table-{$key}" class="table table-bordered">
                <thead>
                <tr>
                    <th>{l s='Código' mod='quartupsearcher'}</th>
                    <th>{l s='Descripción' mod='quartupsearcher'}</th>
                    <th>{l s='Precio/und' mod='quartupsearcher'}</th>
                    <th>{l s='Cantidad' mod='quartupsearcher'}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $products as $product}
                    <tr>
                        <td>{$product.reference}</td>
                        <td>{$product.description}</td>
                        <td>{$product.priceTaxInc}</td>
                        <td><input class="quantity" type="text" id="quantity-searcher" name="quantity-searcher" value="1" /></td>
                        <td>
                            <a class="button ajax_add_to_cart_button btn btn-default"
                               href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}"
                               rel="nofollow"
                               title="Añadir al carrito"
                               {if isset($product.id_product_attribute)}data-id-product-attribute="{$product.id_product_attribute|intval}"{/if}
                               data-id-product="{$product.id|intval}"
                               data-minimal_quantity="1"
                               data-stock="{$product.stock|intval}"
                               data-stock-to-receive="{$product.stockToReceive|intval}"
                               data-reference="{$product.reference}"
                               onclick="$(this).data('minimal_quantity',$('#quantity-searcher').val());add_product_quartup($(this))">
                                <span>{l s='Añadir al carrito' mod='quartupsearcher'}</span>
                            </a>
                        </td>
                    </tr>
                    {if $product.stock > 0}
                        <tr class="msj-{$product.reference}">
                            <td colspan="5" class="text-center">
                                <span class="label label-success">{l s='Artículo disponible '  mod='quartupsearcher'}</span>
                            </td>
                        </tr>
                    {elseif $product.stock <= 0}
                        <tr class="msj-{$product.reference}">
                            <td colspan="5" class="text-center">
                                <span class="label label-danger">{l s='Sin disponibilidad '  mod='quartupsearcher'}</span>
                            </td>
                        </tr>
                    {/if}
                    {if !empty($product.aaToReceive) && $product.stock <= 0}
                        {foreach $product.aaToReceive as $aaToReceive}
                            {if !empty($aaToReceive.dateToReceive)}
                                <tr class="msj-{$product.reference}"  {if $product.stock > 0}style="display:none;"{/if}>
                                    <td colspan="5" class="text-center">
                                        {assign var="fecha" value=DateTime::createFromFormat('Ymd', $aaToReceive.dateToReceive)}
                                        <span class="label label-warning">{l s='Disponibilidad a partir de '  mod='quartupsearcher'}{$fecha->format('d/m/Y')}</span>
                                    </td>
                                </tr>
                            {/if}
                        {/foreach}
                    {elseif $product.stock > 0 && $product.stockToReceive > 0}
                        {foreach $product.aaToReceive as $aaToReceive}
                            {if !empty($aaToReceive.dateToReceive)}
                                <tr class="msj-{$product.reference}" id="stock-parcial-{$product.reference}" {if $product.stock > 0}style="display:none;"{/if}>
                                    <td colspan="5" class="text-center">
                                        {assign var="fecha" value=DateTime::createFromFormat('Ymd', $aaToReceive.dateToReceive)}
                                        <span class="label label-warning">{l s='Articulo Disponible Parcialmente.'  mod='quartupsearcher'}{l s=' Stock Actual: '}
                                            <span class="stock-to-receive">{$product.stockToReceive|intval}</span> {l s=' unds. '} {l s='Resto: Disponibilidad a partir de '  mod='quartupsearcher'}{$fecha->format('d/m/Y')}</span>
                                    </td>
                                </tr>
                            {/if}
                        {/foreach}
                    {/if}
                {/foreach}
                </tbody>
            </table>
        {/foreach}
    </div>
{else}

    <div class="alert alert-danger">
        <p>{l s='Esta referencia no esta en nuestro catálogo.'}</p>
    </div>

{/if}
<!-- /Table quartupsearcher module -->

