{**
 * 2010-2023 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2022 Webkul IN
 * @license LICENSE.txt
 *}

 {if $id_cart_rule}
    <a class="btn btn-link" href="{$link->getAdminLink('AdminCartRules')|escape:'html':'UTF-8'}&updatecart_rule&id_cart_rule={$id_cart_rule}" target="_blank">
        #{$id_cart_rule}
    </a>
{else}
    <a href="{$link->getAdminLink('AdminSlip')|escape:'html':'UTF-8'}&generateVoucher=1&id_order_slip={$row['id_order_slip']}" class="btn btn-default" title="{l s='Generate voucher for credit slip'}">
        <i class="icon-refresh"></i> {l s='Generate Voucher'}
    </a>
{/if}
