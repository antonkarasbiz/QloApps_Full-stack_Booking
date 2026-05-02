{**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*}

{assign var=pdf_style_template value=$smarty.const._PS_PDF_DIR_|cat:'invoice.style-tab.tpl'}
{include file=$pdf_style_template}

<table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
    <thead>
        <tr>
            <th colspan="6" class="header-left small">{l s='BOOKING INFORMATION' pdf='true'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="small white"><span class="bold">{l s='Booking Reference' pdf='true'}</span></td>
            <td class="small white">{$order->reference|escape:'html':'UTF-8'}</td>
            <td class="small white"><span class="bold">{l s='Booking Date' pdf='true'}</span></td>
            <td class="small white">{$booking_date|escape:'html':'UTF-8'}</td>
            <td class="small white"><span class="bold">{l s='Voucher Issue Date' pdf='true'}</span></td>
            <td class="small white">{$issue_date|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white"><span class="bold">{l s='Status' pdf='true'}</span></td>
            <td class="small white">{$order_status|escape:'html':'UTF-8'}</td>
            <td class="small white"><span class="bold">{l s='Total Rooms' pdf='true'}</span></td>
            <td class="small white">{$total_rooms|intval}</td>
            <td class="small white"><span class="bold">{l s='Total Nights' pdf='true'}</span></td>
            <td class="small white">{$total_room_nights|intval}</td>
        </tr>
    </tbody>
</table>

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td height="12"></td>
    </tr>
</table>

<table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
    <thead>
        <tr>
            <th colspan="2" class="header-left small" width="48%">{l s='PROPERTY DETAILS' pdf='true'}</th>
            <th class="small white" width="2%" style="border:none !important;"></th>
            <th colspan="2" class="header-left small" width="50%">{l s='GUEST INFORMATION' pdf='true'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="small white" width="14%"><span class="bold">{l s='Property' pdf='true'}</span></td>
            <td class="small white" width="34%">{$hotel_name|escape:'html':'UTF-8'}</td>
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="small white" width="15%"><span class="bold">{l s='Primary Guest' pdf='true'}</span></td>
            <td class="small white" width="35%">{$guest_name|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white" width="14%"><span class="bold">{l s='Email' pdf='true'}</span></td>
            <td class="small white" width="34%">{$hotel_email|escape:'html':'UTF-8'}</td>
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="small white" width="15%"><span class="bold">{l s='Email' pdf='true'}</span></td>
            <td class="small white" width="35%">{$guest_email|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white" width="14%"><span class="bold">{l s='Phone' pdf='true'}</span></td>
            <td class="small white" width="34%">{$hotel_phone|escape:'html':'UTF-8'}</td>
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="small white" width="15%"><span class="bold">{l s='Phone' pdf='true'}</span></td>
            <td class="small white" width="35%">{$guest_phone|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white" width="14%"><span class="bold">{l s='Address' pdf='true'}</span></td>
            <td class="small white" width="34%">{if $hotel_address}{$hotel_address nofilter}{else}--{/if}</td>
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="small white" width="15%"><span class="bold">{l s='Address' pdf='true'}</span></td>
            <td class="small white" width="35%">{if $guest_address|strip_tags|trim}{$guest_address nofilter}{else}--{/if}</td>
        </tr>
        <tr>
            <td class="small white" width="14%" style="border:none !important;"></td>
            <td class="small white" width="34%" style="border:none !important;"></td>
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="small white" width="15%"><span class="bold">{l s='Total Guests' pdf='true'}</span></td>
            <td class="small white" width="35%">{$total_guests|intval}</td>
        </tr>
    </tbody>
</table>

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td height="12"></td>
    </tr>
</table>

<table class="bordered-table" width="100%" cellpadding="4" cellspacing="0" nobr="true">
    <thead>
        <tr>
            <th colspan="7" class="header-left small">{l s='ROOM DETAILS' pdf='true'}</th>
        </tr>
        <tr>
            <th class="header-left small" width="24%">{l s='Room Description' pdf='true'}</th>
            <th class="header-left small" width="14%">{l s='Tax Rate(s)' pdf='true'}</th>
            <th class="header-left small" width="14%">{l s='Unit Price' pdf='true'}<br />{l s='(Tax excl.)' pdf='true'}</th>
            <th class="header-left small" width="14%">{l s='Rooms' pdf='true'}</th>
            <th class="header-left small" width="11%">{l s='Check-in Date' pdf='true'}</th>
            <th class="header-left small" width="11%">{l s='Check-out Date' pdf='true'}</th>
            <th class="header-left small" width="12%">{l s='Total' pdf='true'}<br />{l s='(Tax excl.)' pdf='true'}</th>
        </tr>
    </thead>
    <tbody>
        {if $room_type_rows}
            {foreach from=$room_type_rows item=room_type_row}
                {cycle values="color_line_even,color_line_odd" assign=bgcolor_class}
                <tr class="{$bgcolor_class}">
                    <td class="small white left" width="24%">{$room_type_row.room_type_name|escape:'html':'UTF-8'}</td>
                    <td class="small white center" width="14%">{$room_type_row.order_detail_tax_label}</td>
                    <td class="small white center" width="14%">{displayPrice currency=$order->id_currency price=$room_type_row.unit_price_tax_excl}</td>
                    <td class="small white left" width="14%">
                        {if $room_type_row.adults <= 9}0{/if}{$room_type_row.adults|intval} {if $room_type_row.adults > 1}{l s='Adults' pdf='true'}{else}{l s='Adult' pdf='true'}{/if}{if $room_type_row.children}, {if $room_type_row.children <= 9}0{/if}{$room_type_row.children|intval} {if $room_type_row.children > 1}{l s='Children' pdf='true'}{else}{l s='Child' pdf='true'}{/if}{/if}<br />{if $room_type_row.rooms <= 9}0{/if}{$room_type_row.rooms|intval} {if $room_type_row.rooms > 1}{l s='Rooms' pdf='true'}{else}{l s='Room' pdf='true'}{/if}
                    </td>
                    <td class="small white center" width="11%" nowrap="nowrap">{$room_type_row.check_in_date|escape:'html':'UTF-8'}</td>
                    <td class="small white center" width="11%" nowrap="nowrap">{$room_type_row.check_out_date|escape:'html':'UTF-8'}</td>
                    <td class="small white center" width="12%">{displayPrice currency=$order->id_currency price=$room_type_row.total_price_tax_excl}</td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td class="white center" colspan="7">{l s='No room details available.' pdf='true'}</td>
            </tr>
        {/if}
    </tbody>
</table>

{if $service_rows}
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td height="12"></td>
        </tr>
    </table>

    <table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
        <thead>
            <tr>
                <th colspan="4" class="header-left small">{l s='SERVICES DETAILS' pdf='true'}</th>
            </tr>
            <tr>
                <th class="header-left small">{l s='Room Ref' pdf='true'}</th>
                <th class="header-left small">{l s='Check-in / Check-out' pdf='true'}</th>
                <th class="header-left small">{l s='Service Name' pdf='true'}</th>
                <th class="center small">{l s='Qty' pdf='true'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$service_rows item=service}
                {cycle values="color_line_even,color_line_odd" assign=bgcolor_class}
                <tr class="{$bgcolor_class}">
                    <td class="small white">{$service.room_ref|escape:'html':'UTF-8'}</td>
                    <td class="small white">{$service.check_in_date|escape:'html':'UTF-8'} - {$service.check_out_date|escape:'html':'UTF-8'}</td>
                    <td class="small white">{$service.name|escape:'html':'UTF-8'}</td>
                    <td class="center small white">{$service.quantity|intval}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/if}

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td height="12"></td>
    </tr>
</table>

{assign var=payment_method_rendered value=false}
{assign var=payment_status_rendered value=false}
<table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
    <tbody>
        <tr>
            <th colspan="2" class="header-left small" width="48%">{l s='PAYMENT INFORMATION' pdf='true'}</th>
            <th class="small white" width="2%" style="border:none !important;"></th>
            <th colspan="2" class="header-left small" width="50%">{l s='TOTAL AMOUNT' pdf='true'}</th>
        </tr>
        {if isset($footer.room_price_tax_excl) && $footer.room_price_tax_excl}
            <tr>
               {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method nofilter}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small" width="35%">{l s='Total Rooms Cost (tax excl.)' pdf='true'}</td>
                <td class="white right small" width="15%">{displayPrice currency=$order->id_currency price=$footer.room_price_tax_excl}</td>
            </tr>
        {/if}
        {if isset($footer.service_products_price_tax_excl) && $footer.service_products_price_tax_excl}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small" width="35%">{l s='Total Products cost (tax excl.)' pdf='true'}</td>
                <td class="white right small" width="15%">{displayPrice currency=$order->id_currency price=$footer.service_products_price_tax_excl}</td>
            </tr>
        {/if}
        {if isset($footer.additional_service_price_tax_excl) && $footer.additional_service_price_tax_excl}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small" width="35%">{l s='Extra Services Cost (tax excl.)' pdf='true'}</td>
                <td class="white right small" width="15%">{displayPrice currency=$order->id_currency price=$footer.additional_service_price_tax_excl}</td>
            </tr>
        {/if}
        {if isset($footer.total_convenience_fee_te) && $footer.total_convenience_fee_te}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small" width="35%">{l s='Convenience Fee (tax excl.)' pdf='true'}</td>
                <td class="white right small" width="15%">{displayPrice currency=$order->id_currency price=$footer.total_convenience_fee_te}</td>
            </tr>
        {/if}
        <tr>
            {if !$payment_method_rendered}
                <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                {assign var=payment_method_rendered value=true}
            {elseif !$payment_status_rendered}
                <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                {assign var=payment_status_rendered value=true}
            {else}
                <td class="small white" width="21%" style="border:none !important;"></td>
                <td class="small white" width="27%" style="border:none !important;"></td>
            {/if}
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="grey small bold" width="35%">{l s='Total (Tax excl.)' pdf='true'}</td>
            <td class="white right small bold" width="15%">{displayPrice currency=$order->id_currency price=$footer.total_without_discount_te}</td>
        </tr>
        {if $footer.total_tax_without_discount > 0}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small bold" width="35%">{l s='Total Tax' pdf='true'}</td>
                <td class="white right small bold" width="15%">{displayPrice currency=$order->id_currency price=$footer.total_tax_without_discount}</td>
            </tr>
        {/if}
        {if isset($footer.product_discounts_tax_incl) && $footer.product_discounts_tax_incl > 0}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small bold" width="35%">{l s='Total Discounts' pdf='true'}</td>
                <td class="white right small bold" width="15%">- {displayPrice currency=$order->id_currency price=$footer.product_discounts_tax_incl}</td>
            </tr>
        {/if}
        <tr>
            {if !$payment_method_rendered}
                <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                {assign var=payment_method_rendered value=true}
            {elseif !$payment_status_rendered}
                <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                {assign var=payment_status_rendered value=true}
            {else}
                <td class="small white" width="21%" style="border:none !important;"></td>
                <td class="small white" width="27%" style="border:none !important;"></td>
            {/if}
            <td class="small white" width="2%" style="border:none !important;"></td>
            <td class="grey small bold big" width="35%">{l s='Final Booking Amount' pdf='true'}</td>
            <td class="white right small bold big" width="15%">{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}</td>
        </tr>
        {if $footer.amount_due > 0}
            <tr>
                {if !$payment_method_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Method' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_method|escape:'html':'UTF-8'}</td>
                    {assign var=payment_method_rendered value=true}
                {elseif !$payment_status_rendered}
                    <td class="grey left small bold" width="21%">{l s='Payment Status' pdf='true'}</td>
                    <td class="white left small" width="27%">{$payment_status|escape:'html':'UTF-8'}</td>
                    {assign var=payment_status_rendered value=true}
                {else}
                    <td class="small white" width="21%" style="border:none !important;"></td>
                    <td class="small white" width="27%" style="border:none !important;"></td>
                {/if}
                <td class="small white" width="2%" style="border:none !important;"></td>
                <td class="grey small bold big" width="35%">{l s='Due Amount' pdf='true'}</td>
                <td class="white right small bold big" width="15%">{displayPrice currency=$order->id_currency price=$footer.amount_due}</td>
            </tr>
        {/if}
    </tbody>
</table>

{if $has_cancellation_policy}
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td height="12"></td>
        </tr>
    </table>

    <table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
        <thead>
            <tr>
                <th colspan="2" class="header-left small">{l s='CANCELLATION POLICY' pdf='true'}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="small white" colspan="2">{$cancellation_policy nofilter}</td>
            </tr>
        </tbody>
    </table>
{/if}

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td height="12"></td>
    </tr>
</table>

<table class="bordered-table" width="100%" cellpadding="5" cellspacing="0" nobr="true">
    <thead>
        <tr>
            <th colspan="2" class="header-left small">{l s='PROPERTY POLICIES' pdf='true'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="small white" width="30%"><span class="bold">{l s='Check-in time' pdf='true'}</span></td>
            <td class="small white" width="70%">{$check_in_time|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white" width="30%"><span class="bold">{l s='Check-out time' pdf='true'}</span></td>
            <td class="small white" width="70%">{$check_out_time|escape:'html':'UTF-8'}</td>
        </tr>
        <tr>
            <td class="small white" width="30%"><span class="bold">{l s='Policies' pdf='true'}</span></td>
            <td class="small white" width="70%">{$payment_policy nofilter}</td>
        </tr>
    </tbody>
</table>

{if isset($HOOK_DISPLAY_PDF) && $HOOK_DISPLAY_PDF}
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td>{$HOOK_DISPLAY_PDF}</td>
        </tr>
    </table>
{/if}
