<?php
/**
* 2010-2022 Webkul.
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
*  @copyright 2010-2022 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class FreeOrder extends PaymentModule
{
    public $active = 1;
    public $name = 'free_order';
    public $displayName = 'free_order';

    public function __construct()
    {
        // Default payment type for Free order should be online
        $this->payment_type = OrderPayment::PAYMENT_TYPE_ONLINE;
    }
}