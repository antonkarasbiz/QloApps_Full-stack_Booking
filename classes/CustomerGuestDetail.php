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

class CustomerGuestDetailCore extends ObjectModel
{
    public $id_customer_guest_detail;
    public $id_customer;
    public $id_gender;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'customer_guest_detail',
        'primary' => 'id_customer_guest_detail',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_gender' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'firstname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 32),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'phone' => array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );


    /**
     * Get the customer guest id by id cart.
     *
     * @param int id_cart
     * @return int|false id_customer_guest_detail if exists
     */
    public static function getCustomerGuestIdByIdCart($id_cart)
    {
        return Db::getInstance()->getValue('
            SELECT `id_customer_guest_detail`
            FROM `'._DB_PREFIX_.'cart_customer_guest`
            WHERE `id_cart` = '.(int) $id_cart
        );
    }

    /**
     * set the customer guest id for the id cart.
     *
     * @param int id_cart
     * @param int id_customer_guest_detail
     * @return bool True on success, false on failure
     */
    public function saveCustomerGuestInCart($idCart, $idCustomerGuestDetail)
    {
        return Db::getInstance()->insert(
            'cart_customer_guest',
            array(
                'id_cart' => (int) $idCart,
                'id_customer_guest_detail' => (int) $idCustomerGuestDetail
            )
        );
    }

    /**
     * removes the customer guest id for the id cart.
     *
     * @param int id_cart
     * @return bool True on success, false on failure
     */
    public function deleteCustomerGuestInCart($idCart)
    {
        return Db::getInstance()->delete(
            'cart_customer_guest',
            ' `id_cart`='.(int) $idCart
        );
    }

    /**
     * Removes this guest's ID from all related cart entries.
     *
     * @return bool True on success, false on failure
     */
    public function deleteCartCustomerGuestByIdGuest()
    {
        return Db::getInstance()->delete(
            'cart_customer_guest',
            '`id_customer_guest_detail` = ' . (int) $this->id
        );
    }

    public function delete()
    {
        $this->deleteCartCustomerGuestByIdGuest();

        return parent::delete();
    }

    /**
     * Deletes the guest profiles created by customer.
     *
     * @param int id_customer
     * @param int offset
     * @return bool True on success, false on failure
     */
    public function deleteCustomerGuestByIdCustomer($idCustomer, $offset = 0)
    {
        $res = true;
        if (($guests = $this->getCustomerGuestsByIdCustomer($idCustomer))
            && ($guests = array_slice($guests, $offset))
        ) {
            foreach ($guests as $guest) {
                $objCustomerGuestDetail = new CustomerGuestDetail((int) $guest['id_customer_guest_detail']);
                $res &= $objCustomerGuestDetail->delete();
            }
        }

        return $res;
    }

    /**
     * Retrieves guest profiles created by a customer using the "booking for someone else" feature.
     *
     * @param int $idCustomer The customer ID
     * @param string $firstname Optional first name filter
     * @param string $lastname Optional last name filter
     * @param string $email Optional email filter
     * @return array List of matching guest profiles
     */
    public function getCustomerGuestsByIdCustomer($idCustomer, $firstname = '', $lastname = '', $email = '')
    {
        $sql = 'SELECT cgd.`id_customer_guest_detail`, cgd.`email`, cgd.`firstname`, cgd.`lastname`, cgd.`phone`
            FROM `' . _DB_PREFIX_ . 'customer_guest_detail` cgd
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_customer_guest` ccg
                ON ccg.`id_customer_guest_detail` = cgd.`id_customer_guest_detail`
            WHERE cgd.`id_customer` = '.(int) $idCustomer;

        if ($firstname !== '') {
            $sql .= ' AND cgd.`firstname` LIKE "%'.pSQL($firstname).'%"';
        }

        if ($lastname !== '') {
            $sql .= ' AND cgd.`lastname` LIKE "%'.pSQL($lastname).'%"';
        }

        if ($email !== '') {
            $sql .= ' AND cgd.`email` LIKE "%'.pSQL($email).'%"';
        }

        $sql .= ' GROUP BY cgd.`id_customer_guest_detail` ORDER BY cgd.`date_add` DESC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Retrieves guest information using the Id.
     *
     * @param int id_customer_guest_detail
     * @return array customer guest details.
     */
    public static function getCustomerGuestDetail($idCustomerGuestDetail)
    {
        return Db::getInstance()->getRow('
            SELECT `id_gender`, `firstname`, `lastname`, `email`, `phone`
            FROM `'._DB_PREFIX_.'customer_guest_detail`
            WHERE `id_customer_guest_detail` = '.(int) $idCustomerGuestDetail
        );
    }

    public static function getIdCustomerGuest($email, $idCustomer = null, $idCart = 0)
    {
        return Db::getInstance()->getValue(
            'SELECT cgd.`id_customer_guest_detail` FROM `'._DB_PREFIX_.'customer_guest_detail` as cgd
            LEFT JOIN `'._DB_PREFIX_.'cart_customer_guest` ccg
            ON ccg.`id_customer_guest_detail` = cgd.`id_customer_guest_detail`
            WHERE cgd.`email` = "'.pSQL($email).'"'.
            (!is_null($idCart) ? ' AND (ccg.`id_cart` = '.(int) $idCart.' '. (($idCart) ? ')'  : ' OR ISNULL(ccg.`id_cart`)) ') : ' ').
            (!is_null($idCustomer) ? ' AND cgd.`id_customer` = '.(int) $idCustomer : ' ')
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

        $className = 'CustomerGuestDetail';
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
