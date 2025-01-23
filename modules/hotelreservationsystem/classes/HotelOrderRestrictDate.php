<?php
/**
* 2010-2020 Webkul.
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
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class HotelOrderRestrictDate extends ObjectModel
{
    public $id;
    public $id_hotel;
    public $use_global_max_booking_offset;
    public $max_booking_offset;
    public $use_global_min_booking_offset;
    public $min_booking_offset;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'htl_order_restrict_date',
        'primary' => 'id',
        'fields' => array(
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'use_global_max_booking_offset' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'max_booking_offset' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'use_global_min_booking_offset' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'min_booking_offset' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function getDataByHotelId($idHotel)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'htl_order_restrict_date` ord WHERE ord.`id_hotel` = '.(int) $idHotel
        );
    }

    /*Max date of ordering for order restrict*/
    public static function getMaxOrderDate($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_max_booking_offset']) {
            return date('Y-m-d', strtotime('+ '.$result['max_booking_offset'].' days'));
        }

        if ($globalBookingDate = Configuration::get('GLOBAL_MAX_BOOKING_OFFSET')) {
            return date('Y-m-d', strtotime('+ '.$globalBookingDate.' days'));
        }

        return 0;
    }

    /**
     * @param int $id_hotel
     * @return int|false
     */
    public static function getMaxBookingOffset($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_max_booking_offset']) {
            return $result['max_booking_offset'];
        }

        if ($globalBookingDate = Configuration::get('GLOBAL_MAX_BOOKING_OFFSET')) {
            return $globalBookingDate;
        }

        return 0;
    }


    /**
     * @param int $id_hotel
     * @return int|false
     */
    public static function getMinBookingOffset($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_min_booking_offset']) {
            return (int) $result['min_booking_offset'];
        }

        $globalPreparationTime = Configuration::get('GLOBAL_MIN_BOOKING_OFFSET');
        if ($globalPreparationTime != '0') {
            return (int) $globalPreparationTime;
        }

        return false;
    }

    public static function validateOrderRestrictDateOnPayment(&$controller)
    {
        if ($errors = HotelCartBookingData::validateCartBookings()) {
            $controller->errors = array_merge($controller->errors, $errors);

            return true;
        }

        return false;
    }
}
