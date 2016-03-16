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
    <pre class="hidden">{$product_searcher|@print_r}</pre>
    <div class="quartupsearcher-table">
        <table id="quartupsearcher-table" class="table table-bordered">
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
            {foreach $product_searcher as $product}
                <tr>
                    <td>{$quartupsearch_query}</td>
                    <td>{$product.description}</td>
                    <td>{$product.priceTaxInc}</td>
                    <td><input class="quantity" type="text" id="quantity-searcher" name="quantity-searcher" value="1" /></td>
                    <td>
                        <a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="Añadir al carrito" {if isset($product.id_product_attribute)}data-id-product-attribute="{$product.id_product_attribute|intval}"{/if} data-id-product="{$product.id|intval}" data-minimal_quantity="1" onclick="$(this).data('minimal_quantity',$('#quantity-searcher').val());">
                            <span>{l s='Añadir al carrito' mod='quartupsearcher'}</span>
                        </a>
                    </td>
                </tr>
                {if !empty($product.aaToReceive)}
                    {foreach $product.aaToReceive as $aaToReceive}
                        {if !empty($aaToReceive.dateToReceive)}
                            <tr>
                                <td colspan="5">
                                    {assign var="fecha" value=DateTime::createFromFormat('Ymd', $aaToReceive.dateToReceive)}
                                    <span class="label label-warning">{l s='Disponibilidad a partir de '  mod='quartupsearcher'}{$fecha->format('d/m/Y')}</span>
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
            </tbody>
        </table>
    </div>
{/if}
<!-- /Table quartupsearcher module -->

