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

class HotelRoomTypeFeaturePricingRule extends ObjectModel
{
    public $id_feature_price;
    public $date_selection_type;
    public $date_from;
    public $date_to;
    public $is_special_days_exists;
    public $special_days;
    public $date_add;
    public $date_upd;
    public static $definition = array(
        'table' => 'htl_room_type_feature_pricing_rule',
        'primary' => 'id_feature_price_rule',
        'multilang' => false,
        'fields' => array(
            'id_feature_price' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'date_from' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'date_to' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'is_special_days_exists' => array('type' => self::TYPE_INT, 'required' => true),
            'date_selection_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'special_days' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        )
    );

    protected $webserviceParameters = array(
        'objectsNodeName' => 'price_rules',
        'objectNodeName' => 'price_rule',
        'fields' => array(
            'id_feature_price' => array(
                'xlink_resource' => array(
                    'resourceName' => 'feature_prices',
                )
            ),
        )
    );

    public function getRulesByIdFeaturePrice($idFeaturePrice)
    {
        $sql = 'SELECT *, id_feature_price_rule AS `id` FROM `'._DB_PREFIX_.$this->table.'`
            WHERE `id_feature_price` ='.(int) $idFeaturePrice;

        return Db::getInstance()->executeS($sql);
    }

    public function deleteFeaturePriceRulesById($featurePriceRules)
    {
        $res = true;
        if ($featurePriceRules) {
            foreach ($featurePriceRules as $idFeaturePriceRules) {
                $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRule($idFeaturePriceRules);
                $res &= $objFeaturePriceRule->delete();
            }
        }

        return $res;
    }

}
