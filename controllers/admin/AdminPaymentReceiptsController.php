<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class AdminPaymentReceiptsControllerCore extends AdminControllerCore
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->context = Context::getContext();
        $this->lang = false;
        $this->table = 'order_payment';
       
        $this->fields_options = array(
            'general' => array(
                'title' =>    $this->l('Payment receipts options'),
                'fields' =>    array(
                    'PS_PAYMENT_RECEIPTS' => array(
                        'title' => $this->l('Enable Receipts'),
                        'desc' => $this->l('If enabled, your customers will receive a receipt for their purchase(s).'),
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'PS_PAYMENT_RECEIPTS_PREFIX' => array(
                        'title' => $this->l('Receipt prefix'),
                        'desc' => $this->l('Prefix used for receipt name (e.g. #IN00001).'),
                        'size' => 6,
                        'type' => 'textLang'
                    ),
                    'PS_PAYMENT_RECEIPTS_USE_YEAR' => array(
                        'title' => $this->l('Add current year to receipt number'),
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'PS_PAYMENT_RECEIPTS_RESET' => array(
                        'title' => $this->l('Reset Receipt progressive number at beginning of the year'),
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'PS_PAYMENT_RECEIPTS_START_NUMBER' => array(
                        'title' => $this->l('Receipt number'),
                        'desc' => sprintf($this->l('The next receipt will begin with this number, and then increase with each additional receipt. Set to 0 if you want to keep the current number (which is #%s).'), Order::getLastPaymentNumber() + 1),
                        'size' => 6,
                        'type' => 'text',
                        'cast' => 'intval'
                    ),
                    'PS_PAYMENT_RECEIPTS_LEGAL_FREE_TEXT' => array(
                        'title' => $this->l('Legal free text'),
                        'desc' => $this->l('Use this field to display additional text on your receipt, like specific legal information. It will appear below the payment methods summary.'),
                        'size' => 50,
                        'type' => 'textareaLang',
                    ),
                    'PS_PAYMENT_RECEIPTS_FREE_TEXT' => array(
                        'title' => $this->l('Footer text'),
                        'desc' => $this->l('This text will appear at the bottom of the receipt, below your company details.'),
                        'size' => 50,
                        'type' => 'textLang',
                    ),
                    'PS_PDF_USE_CACHE' => array(
                        'title' => $this->l('Use the disk as cache for PDF receipts'),
                        'desc' => $this->l('Saves memory but slows down the PDF generation.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    )
                ),
                'submit' => array('title' => $this->l('Save'))
            )
        );
    }

    public function initContent()
    {
        $this->display = 'edit';
        $this->initTabModuleList();
        $this->initToolbar();
        $this->initPageHeaderToolbar();
        $this->table = 'order_payment';
        $this->content .= $this->renderOptions();

        $this->context->smarty->assign(array(
            'content' => $this->content,
            'url_post' => self::$currentIndex.'&token='.$this->token,
            'show_page_header_toolbar' => $this->show_page_header_toolbar,
            'page_header_toolbar_title' => $this->page_header_toolbar_title,
            'page_header_toolbar_btn' => $this->page_header_toolbar_btn
        ));
    }

    public function initToolbarTitle()
    {
        $this->toolbar_title = array_unique($this->breadcrumbs);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        unset($this->page_header_toolbar_btn['cancel']);
    }

    public function postProcess()
    {
        parent::postProcess();
    }

    public function beforeUpdateOptions()
    {
        if ((int)Tools::getValue('PS_PAYMENT_RECEIPTS_START_NUMBER') != 0 && (int)Tools::getValue('PS_PAYMENT_RECEIPTS_START_NUMBER') <= Order::getLastPaymentNumber()) {
            $this->errors[] = $this->l('Invalid receipt number.').Order::getLastPaymentNumber().')';
        }
    }
}

