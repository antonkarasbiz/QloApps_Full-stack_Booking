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
    <div id="new_product">
        <input type="hidden" id="add_product_product_id" name="add_product[product_id]" value="0" />
        <div class="form-group">
            <label class="control-label">{l s='Product:'}</label>
            <div class="input-group">
                <input type="text" id="add_product_product_name" class="form-control" value="" placeholder="{l s='Enter the name of the product'}" />
                <div class="input-group-addon">
                    <i class="icon-search"></i>
                </div>
            </div>
        </div>
        <div class="add_product_fields" style="display:none;">
            {hook h='displayAdminOrderAddRoomFormFieldsBefore'}
            <div class="row form-group">
                <div class="col-sm-6">
                    <label class="control-label">{l s='Price (tax excl.)'}</label>
                    <div class="input-group">
                        {if $currency->format % 2}<div class="input-group-addon">{$currency->sign}</div>{/if}
                        <input class="form-control" type="text" name="add_product[product_price_tax_excl]" id="add_product_product_price_tax_excl" value=""  disabled="disabled"/>
                        {if !($currency->format % 2)}<div class="input-group-addon">{$currency->sign}</div>{/if}
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="control-label">{l s='Price (tax incl.)'}</label>
                    <div class="input-group">
                        {if $currency->format % 2}<div class="input-group-addon">{$currency->sign}</div>{/if}
                        <input class="form-control" type="text" name="add_product[product_price_tax_incl]" id="add_product_product_price_tax_incl" value=""  disabled="disabled"/>
                        {if !($currency->format % 2)}<div class="input-group-addon">{$currency->sign}</div>{/if}
                    </div>
                </div>
            </div>

            <div class="">
                <div class="row">
                    <div class="productQuantity form-group col-sm-6">
                        <label class="control-label">{l s='Quantity'}</label>
                        <input type="number" class="form-control" name="add_product[product_quantity]" id="add_product_product_quantity" value="1" disabled="disabled" min="1"/>
                    </div>

                    <div class="productOptions form-group col-sm-6" style="display: none;">
                        <label class="control-label">{l s='Variant'}</label>
                        <select name="add_product[product_option]" id="add_product_product_option">
                        </select>
                    </div>
                </div>
            </div>


            {if sizeof($invoices_collection)}
                <div class="form-group">
                    <label class="control-label">{l s='Invoice'}</label>
                    <select class="form-control" name="add_product[invoice]" id="add_product_product_invoice" disabled="disabled">
                        <optgroup class="existing" label="{l s='Existing'}">
                            {foreach from=$invoices_collection item=invoice}
                            <option value="{$invoice->id}">{$invoice->getInvoiceNumberFormatted($current_id_lang)}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{l s='New'}">
                            <option value="0">{l s='Create a new invoice'}</option>
                        </optgroup>
                    </select>
                </div>
            {/if}
        </div>
        <button type="button" class="btn btn-default" id="submitAddProduct" disabled="disabled" style="display:none;"></button>
    </div>

    {if isset($loaderImg) && $loaderImg}
        <div class="loading_overlay">
            <img src='{$loaderImg}' class="loading-img"/>
        </div>
    {/if}
</div>
