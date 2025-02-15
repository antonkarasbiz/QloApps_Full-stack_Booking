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

class CartCustomerGuestDetailCore extends ObjectModel
{
    public $id_customer_guest_detail;
    public $id_cart;
    public $id_gender;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'cart_customer_guest_detail',
        'primary' => 'id_customer_guest_detail',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_gender' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'firstname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'phone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getCartCustomerGuest($id_cart)
    {
        return Db::getInstance()->getValue('
            SELECT `id_customer_guest_detail`
            FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = '.(int)$id_cart
        );
    }

    public function add($auto_date = true, $null_values = false)
    {
        if ($res = parent::add($auto_date, $null_values)) {
            $objCart = new Cart($this->id_cart);
            Db::getInstance()->insert('customer_guest_detail_mapping', array(
                'id_customer' => (int) $objCart->id_customer,
                'id_customer_guest_detail' => (int)$this->id
            ));
        }

        return $res;
    }

    public function delete()
    {
        if ($res = parent::delete()) {
            $objCart = new Cart($this->id_cart);
            Db::getInstance()->delete('customer_guest_detail_mapping',
                '`id_customer` = '.(int) $objCart->id_customer.'
                AND `id_customer_guest_detail`='.(int) $this->id
            );
        }

        return $res;
    }

    public function getCustomerRelatedGuestDetails($idCustomer, $firstname = false, $lastname = false, $email = false)
    {
        return Db::getInstance()->executeS('
            SELECT cgd.`id_customer_guest_detail`, cgd.`email`, cgd.`firstname`, cgd.`lastname`, cgd.`phone`, cgd.`id_cart`
            FROM `'._DB_PREFIX_.'customer_guest_detail_mapping` cgdm
            LEFT JOIN `'._DB_PREFIX_.'cart_customer_guest_detail` cgd
            ON (cgdm.`id_customer_guest_detail` = cgd.`id_customer_guest_detail`)
            WHERE cgdm.`id_customer` = '.(int)$idCustomer.
            (($firstname !== false && $firstname != '') ? ' AND cgd.`firstname` LIKE "%'.$firstname.'%"' : ' ').
            (($lastname !== false && $lastname != '') ? ' AND cgd.`lastname` LIKE "%'.$lastname.'%"' : ' ').
            (($email !== false && $email != '') ? ' AND cgd.`email` LIKE "%'.$email.'%"' : ' ')
        );
    }

    public static function getCustomerGuestDetail($id_customer_guest_detail)
    {
        return Db::getInstance()->getRow('
            SELECT `id_gender`, `firstname`, `lastname`, `email`, `phone`
            FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_customer_guest_detail` = '.(int)$id_customer_guest_detail
        );
    }

    public static function getIdCustomerGuest($email)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_customer_guest_detail` FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = 0 AND `email` = "'.pSQL($email).'"'
        );
    }

    public static function getCustomerPhone($email)
    {
        return Db::getInstance()->getValue(
            'SELECT `phone` FROM `'._DB_PREFIX_.'cart_customer_guest_detail`
            WHERE `id_cart` = 0 AND `email` = "'.pSQL($email).'"'
        );
    }

    public function validateGuestInfo()
    {
        $isValid = true;
        if (!trim($this->firstname) || !Validate::isName($this->firstname)) {
            $isValid = false;
        }
        if (!trim($this->lastname) || !Validate::isName($this->lastname)) {
            $isValid = false;
        }
        if (!trim($this->email) || !Validate::isEmail($this->email)) {
            $isValid = false;
        }
        if (!trim($this->phone) || !Validate::isPhoneNumber($this->phone)) {
            $isValid = false;
        }

        $className = 'CartCustomerGuestDetail';
        $rules = call_user_func(array($className, 'getValidationRules'), $className);

        if (isset($rules['size']['firstname'])) {
            if (Tools::strlen(trim($this->firstname)) > $rules['size']['firstname']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['lastname'])) {
            if (Tools::strlen(trim($this->lastname)) > $rules['size']['lastname']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['email'])) {
            if (Tools::strlen(trim($this->email)) > $rules['size']['email']) {
                $isValid = false;
            }
        }
        if (isset($rules['size']['phone'])) {
            if (Tools::strlen(trim($this->phone)) > $rules['size']['phone']) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}
