/*
* Copyright since 2010 Webkul.
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
*/

$(document).ready(function() {
    if ($('#PS_CUSTOMER_SERVICE_CONTACT_ALLOW_on').prop('checked')) {
        $('[name="PS_CUSTOMER_SERVICE_EMAIL_MESSAGE"]').closest('.form-group').hide();
    }

    $(document).on('change', '[name="PS_CUSTOMER_SERVICE_CONTACT_ALLOW"]', function() {
        if ($('#PS_CUSTOMER_SERVICE_CONTACT_ALLOW_on').prop('checked')) {
            $('[name="PS_CUSTOMER_SERVICE_EMAIL_MESSAGE"]').closest('.form-group').hide();
        } else {
            $('[name="PS_CUSTOMER_SERVICE_EMAIL_MESSAGE"]').closest('.form-group').show();
        }
    });
});
