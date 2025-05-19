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

<div class="modal-body">
    <div id="edit_product">
        <input type="hidden" id="edit_product_product_id" name="edit_product[product_id]" value="{$ServiceProductOrderDetail->id_product}" />
        <input type="hidden" id="edit_product_id_service_product_order_detail" name="edit_product[id_service_product_order_detail]" value="{$ServiceProductOrderDetail->id}" />
        <input type="hidden" id="edit_product_id_order" name="id_order" value="{$order->id}" />
        <div class="edit_product_fields">
            {hook h='displayAdminOrderEditProductFieldsBefore'}
            <div class="row form-group">
                <div class="col-sm-6">
                    <label class="control-label">{l s='Unit Price (tax excl.)'}</label>
                    <div class="input-group">
                        {if $currency->format % 2}<div class="input-group-addon">{$currency->sign}</div>{/if}
                        <input class="form-control" type="text" name="edit_product[product_price_tax_excl]" id="edit_product_product_price_tax_excl" value="{$ServiceProductOrderDetail->unit_price_tax_excl}"/>
                        {if !($currency->format % 2)}<div class="input-group-addon">{$currency->sign}</div>{/if}
                    </div>
                </div>
                <div class="productQuantity col-sm-6" {if !$objProduct->allow_multiple_quantity} style="display:none" {/if}>
                    <label class="control-label">{l s='Quantity'}</label>
                    <input type="number" class="form-control" name="edit_product[product_quantity]" id="edit_product_product_quantity" value="{$ServiceProductOrderDetail->quantity}" min="1"/>
                </div>
            </div>

            <div class="product_invoice" style="display: none;">
                <select name="product_invoice" class="edit_product_invoice">
                    {foreach from=$invoices_collection item=invoice}
                        <option value="{$invoice->id}" {if $invoice->id == $data.id_order_invoice}selected="selected"{/if}>
                            #{Configuration::get('PS_INVOICE_PREFIX', $current_id_lang, null, $order->id_shop)}{'%06d'|sprintf:$invoice->number}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <button type="button" class="btn btn-default" id="submitAddProduct" disabled="disabled" style="display:none;"></button>
    </div>

    {if isset($loaderImg) && $loaderImg}
        <div class="loading_overlay">
            <img src='{$loaderImg}' class="loading-img"/>
        </div>
    {/if}
</div>
