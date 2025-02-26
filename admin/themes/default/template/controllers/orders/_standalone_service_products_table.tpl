{*
* Since 2010 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright Since 2010 Webkul IN
*  @license   https://store.webkul.com/license.html
*}

<div class="row">
    <div class="col-lg-12">
        <table class="table" id="customer_products_details">
            <thead>
                <tr>
                    <th class="text-center"><span class="title_box">{l s='Image'}</th>
                    <th class="text-center"><span class="title_box">{l s='Name'}</span></th>
                    <th class="text-center"><span class="title_box">{l s='Quantity'}</span></th>
                    <th class="text-center"><span class="title_box">{l s='Price (Tax excl.)'}</span></th>
                    <th class="text-center"><span class="title_box">{l s='Total Tax'}</span></th>
                    <th class="text-center"><span class="title_box">{l s='Total Price (Tax incl.)'}</span></th>
                    {if isset($refundReqProducts) && $refundReqProducts}
                        <th class="text-center"><span class="title_box">{l s='Refund State'}</span></th>
                        <th class="text-center"><span class="title_box">{l s='Refunded amount'}</span></th>
                    {/if}
                    {if ($can_edit && !$order->hasBeenDelivered())}
                        <th class="text-right"><span class="title_box">{l s='Edit Order'}</th>
                    {/if}
                </tr>
            </thead>
            <tbody>
            {if $standalone_service_products}
                {foreach from=$standalone_service_products item=product}
                    {* Include product line partial *}
                    {if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
                        {assign var=product_price value=($product['unit_price_tax_excl'])}
                    {else}
                        {assign var=product_price value=$product['unit_price_tax_incl']}
                    {/if}
                    <tr class="product-line-row" data-id_product="{$product.id_product}" data-id_order_detail="{$product.id_order_detail}" data-id_room_type_service_product_order_detail="{$product.id_room_type_service_product_order_detail}">
                        <td class="text-center">
                            {if isset($product.image) && $product.image->id}{$product.image_tag}{/if}
                        </td>
                        <td class="text-center">
                            <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}&amp;id_product={$product['id_product']|intval}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}">
                                <span class="productName">{$product['name']}{if $product['option_name']} : {$product['option_name']}{/if}</span><br />
                            </a>
                        </td>
                        <td class="text-center">
                            <span class="">{(int)$product['quantity']}</span>
                        </td>
                        <td class="text-center unit_price_tax_excl">
                            <p>{displayPrice price=$product.total_price_tax_excl currency=$currency->id}</p>
                            <p class="help-block">{l s='Unit price'} : {displayPrice price=$product.unit_price_tax_excl currency=$currency->id}</p>
                        </td>
                        <td class="text-center">
                            <span>{displayPrice price=($product.total_price_tax_incl - $product.total_price_tax_excl) currency=$currency->id}</span>
                        </td>
                        <td class="text-center">
                            <span>{displayPrice price=$product.total_price_tax_incl currency=$currency->id}</span>
                        </td>
                        {if (isset($refundReqProducts) && $refundReqProducts)}
                            <td class="text-center">
                                {if $product.id_room_type_service_product_order_detail|in_array:$refundReqProducts}
                                    {if $product.is_cancelled}
                                        <span class="badge badge-danger">{l s='Cancelled'}</span>
                                    {elseif isset($product.refund_info) && (!$product.refund_info.refunded || $product.refund_info.id_customization)}
                                        <span class="badge" style="background-color:{$product.refund_info.color|escape:'html':'UTF-8'}">{$product.refund_info.name|escape:'html':'UTF-8'}</span>
                                    {else}
                                        <span>--</span>
                                    {/if}
                                {else}
                                    <span>--</span>
                                {/if}
                            </td>
                            <td class="text-center">
                                {if $product.is_refunded && isset($product.refund_info) && $product.refund_info}
                                    {convertPriceWithCurrency price=$product.refund_info.refunded_amount currency=$currency->id}
                                {else}
                                    --
                                {/if}
                            </td>
                        {/if}
                        {if ($can_edit && !$order->hasBeenDelivered())}
                            <td class="room_invoice" style="display: none;">
                            {if sizeof($invoices_collection)}
                            <select name="product_invoice" class="edit_product_invoice">
                                {foreach from=$invoices_collection item=invoice}
                                <option value="{$invoice->id}" {*{if $invoice->id == $product['id_order_invoice']}selected="selected"{/if}*}>
                                    #{Configuration::get('PS_INVOICE_PREFIX', $current_id_lang, null, $order->id_shop)}{'%06d'|sprintf:$invoice->number}
                                </option>
                                {/foreach}
                            </select>
                            {else}
                            &nbsp;
                            {/if}
                            </td>
                            <td class="product_action text-right">
                                {* edit/delete controls *}
                                <div class="btn-group">
                                    {* <button type="button" class="btn btn-default delete_product_line">
                                        <i class="icon-trash"></i>
                                        {l s='Delete'}
                                    </button> *}
                                    <button type="button" class="btn btn-default edit_product_change_link" data-product_line_data="{$product|json_encode|escape}">
                                        <i class="icon-pencil"></i>
                                        {l s='Edit'}
                                    </button>
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="#" class="delete_product_line">
                                                <i class="icon-trash"></i>
                                                {l s='Delete'}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                {* Update controls *}
                                {* <button type="button" class="btn btn-default submitProductChange" style="display: none;">
                                    <i class="icon-ok"></i>
                                    {l s='Update'}
                                </button>
                                <button type="button" class="btn btn-default cancel_product_change_link" style="display: none;">
                                    <i class="icon-remove"></i>
                                    {l s='Cancel'}
                                </button> *}
                            </td>
                        {/if}
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <td>{l s='No Data Found.'}</td>
                </tr>
            {/if}
            {* Include product line partial *}
            {* {include file='controllers/orders/_new_service_product.tpl'} *}
            </tbody>
        </table>
    </div>
</div>