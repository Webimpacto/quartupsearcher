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
            <input class="quartupsearcher_query" type="text" id="quartupsearcher_query_block" name="search_query" value="{if isset($search_query) && $search_query}{$search_query|escape:'html':'UTF-8'|stripslashes}{/if}" />
            <input type="submit" name="quartupsearcher_button" id="quartupsearcher_button" class="btn btn-primary" value="{l s='Buscar' mod='quartupsearcher'}" />
        </p>
    </form>
</div>
<!-- /Block quartupsearcher module -->
<!-- Table quartupsearcher module -->
<pre class="hidden">{$product_searcher|@print_r}</pre>
{if isset($product_searcher) && $product_searcher}
    <div class="quartupsearcher-table">
        <table id="quartupsearcher-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>{l s='Código'}</th>
                    <th>{l s='Descripción'}</th>
                    <th>{l s='Precio/und'}</th>
                    <th>{l s='Cantidad'}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {foreach $product_searcher as $product}
                    <tr id="" class="">
                        <td>{$search_query}</td>
                        <td>{$product.description}</td>
                        <td>{convertPrice price=$product.priceTaxInc}</td>
                        <td><input class="quantity" type="text" id="quantity-searcher" name="quantity-searcher" value="" /></td>
                        <td>
                            <a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product-attribute="{*$product.id_product_attribute|intval*}" data-id-product="{*$product.id_product|intval*}" data-minimal_quantity="{*if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1*}{*$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if*}">
                                <span>{l s='Añadir'}</span>
                            </a>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/if}
<!-- /Table quartupsearcher module -->

