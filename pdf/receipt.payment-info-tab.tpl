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

{if isset($payment_list) && $payment_list}
	<table class="bordered-table" width="100%" cellpadding="4" cellspacing="0">
		<thead>
            <tr>
                <th class="header" colspan="5">{l s='Payment Details' pdf='true'}</th>
			</tr>
			<tr>
                <th class="product header small">{l s='Payment Date' pdf='true'}</th>
                <th class="product header small">{l s='Payment Method' pdf='true'}</th>
                <th class="product header small">{l s='Payment Type' pdf='true'}</th>
                <th class="product header small">{l s='Transaction ID' pdf='true'}</th>
                <th class="product header small">{l s='Amount' pdf='true'}</th>
			</tr>
		</thead>
		<tbody>
                {foreach from=$payment_list key=rm_k item=payment}
                    {cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
                    <tr class="white  {$bgcolor_class}">
                        <td class="product center">
                            {dateFormat date=$payment->date_add full=true}
                        </td>
                        <td class="product center">
                            {$payment->payment_method}
                        </td>
                        <td class="product center">
                            {$payment->payment_type}
                        </td>
                        <td class="product center">
                            {$payment->transaction_id}
                        </td>
                        <td class="product center">
                            {displayPrice currency=$payment->id_currency price=$payment->amount}
                        </td>
                    </tr>
                {/foreach}
		</tbody>
	</table>
{/if}