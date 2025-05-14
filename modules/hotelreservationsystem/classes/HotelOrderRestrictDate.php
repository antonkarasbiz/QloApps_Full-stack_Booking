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

    /**
     * @param int $id_hotel
     * @return array hote wise restriction.
     */
    public static function getDataByHotelId($idHotel)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'htl_order_restrict_date` ord WHERE ord.`id_hotel` = '.(int) $idHotel
        );
    }

    /**
     * @param int $id_hotel
     * @return string Max date of ordering for order restrict
     */
    public static function getMaxOrderDate($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_max_booking_offset']) {
            return date('Y-m-d H:i:s', strtotime('+ '.($result['max_booking_offset'] + 1).' days'));
        }

        // since this cannot be zero, this function will always return a date.
        $globalBookingDate = (int) Configuration::get('PS_MAX_BOOKING_OFFSET');

        return date('Y-m-d H:i:s', strtotime('+ '.($globalBookingDate + 1).' days'));
    }

    /**
     * @param int $id_hotel
     * @return int Maximum allowable number of days between booking date and check-in date.
     */
    public static function getMaximumBookingOffset($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_max_booking_offset']) {
            return $result['max_booking_offset'];
        }

        return (int) Configuration::get('PS_MAX_BOOKING_OFFSET');
    }

    /**
     * @param int $id_hotel
     * @return int Minimum number of days required between booking and check-in.
     */
    public static function getMinimumBookingOffset($idHotel)
    {
        $result = self::getDataByHotelId($idHotel);
        if (is_array($result) && count($result) && !$result['use_global_min_booking_offset']) {
            return (int) $result['min_booking_offset'];
        }

        return (int) Configuration::get('PS_MIN_BOOKING_OFFSET');
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
