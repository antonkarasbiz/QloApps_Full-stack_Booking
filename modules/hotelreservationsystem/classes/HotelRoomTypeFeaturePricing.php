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

class HotelRoomTypeFeaturePricing extends ObjectModel
{
    public $id_product;
    public $id_cart = 0;
    public $id_guest = 0;
    public $id_room = 0;
    public $feature_price_name;
    public $impact_way;
    public $impact_type;
    public $impact_value;
    public $active;
    public $date_add;
    public $date_upd;

    public $groupBox;

    const DATE_SELECTION_TYPE_RANGE = 1;
    const DATE_SELECTION_TYPE_SPECIFIC = 2;

    const IMPACT_WAY_DECREASE = 1;
    const IMPACT_WAY_INCREASE = 2;
    const IMPACT_WAY_FIX_PRICE = 3;

    const IMPACT_TYPE_PERCENTAGE = 1;
    const IMPACT_TYPE_FIXED_PRICE = 2;

    protected $moduleInstance;

    public static $definition = array(
        'table' => 'htl_room_type_feature_pricing',
        'primary' => 'id_feature_price',
        'multilang' => true,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_room' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'impact_way' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'impact_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'impact_value' => array('type' => self::TYPE_FLOAT, 'required' => true),
            'active' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            //lang fields
            'feature_price_name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => true,
                'size' => 128
            ),
    ));

    protected $webserviceParameters = array(
        'objectsNodeName' => 'feature_prices',
        'objectNodeName' => 'feature_price',
        'fields' => array(
            'id_product' => array(
                'xlink_resource' => array(
                    'resourceName' => 'room_types',
                )
            ),
        ),
        'associations' => array(
            'groups' => array('resource' => 'group'),
            'price_rules' => array(
                'resource' => 'price_rule',
                'getter' => 'getWsAdvancePriceRule',
                'setter' => 'setWsAdvancePriceRule',
                'fields' => array(
                    'id' => array(),
                    'date_from' => array(),
                    'date_to' => array(),
                    'date_selection_type' => array(),
                    'is_special_days_exists' => array(),
                    'special_days' => array(),
                ),
            ),
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->moduleInstance = Module::getInstanceByName('hotelreservationsystem');
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = true)
    {
        $return = parent::add($autodate, $null_values);

        // call to add/update all the group entries
        $this->updateGroup($this->groupBox);

        return $return;
    }

    public function update($nullValues = false)
    {
        // first call to add/update all the group entries
        $this->updateGroup($this->groupBox);
        return parent::update($nullValues);
    }

    public function delete()
    {
        // first call to delete all the group entries
        $this->cleanGroups();
        $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRule();
        if ($oldFeaturePriceRules = $objFeaturePriceRule->getRulesByIdFeaturePrice($this->id)) {
            $objFeaturePriceRule->deleteFeaturePriceRules($oldFeaturePriceRules);
        }

        return parent::delete();
    }

    public function getExistingFeaturePriceRules(
        $idRoomType,
        $groups,
        $idFeaturePrice,
        $featurePriceRules
    ) {
        $sql = 'SELECT *, GROUP_CONCAT(rtfpg.`id_group`) AS id_group FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing_rule` rtfpr
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing` rtfp
            ON (rtfpr.`id_feature_price` = rtfp.`id_feature_price` AND rtfp.`id_product`='.(int) $idRoomType.')
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_group` rtfpg
            ON (rtfp.`id_feature_price` = rtfpg.`id_feature_price` AND rtfpg.`id_group` IN ('.pSQL(implode(', ',$groups)).'))
            WHERE  rtfpr.`id_feature_price`!='.(int) $idFeaturePrice.' AND rtfp.`active`= 1';
        $sqlWhere = '';
        foreach ($featurePriceRules as $featurePriceRule) {
            if ($sqlWhere != '') {
                $sqlWhere .= ' OR ';
            }

            $dateFrom = date('Y-m-d', strtotime($featurePriceRule['date_from']));
            $dateTo = date('Y-m-d', strtotime($featurePriceRule['date_to']));
            if ($featurePriceRule['date_selection_type'] == self::DATE_SELECTION_TYPE_SPECIFIC) {
                $sqlWhere .= ' (rtfpr.`date_selection_type` = '.(int) self::DATE_SELECTION_TYPE_SPECIFIC.'
                AND rtfpr.`date_from` = \''.pSQL($dateFrom).'\')';
            } else if ($featurePriceRule['date_selection_type'] == self::DATE_SELECTION_TYPE_RANGE) {
                if ($featurePriceRule['is_special_days_exists']) {
                    $sqlWhere .= ' (rtfpr.`is_special_days_exists`=1
                    AND rtfpr.`date_from` < \''.pSQL($dateTo).'\'
                    AND rtfpr.`date_to` > \''.pSQL($dateFrom).'\')';
                } else {
                    $sqlWhere .= ' (rtfpr.`date_selection_type` = '.(int) self::DATE_SELECTION_TYPE_RANGE.'
                    AND rtfpr.`is_special_days_exists`=0
                    AND rtfpr.`date_from` <= \''.pSQL($dateTo).'\'
                    AND rtfpr.`date_to` >= \''.pSQL($dateFrom).'\')';
                }
            }
        }

        if ($sqlWhere != '') {
            $sqlWhere = ' AND ('.$sqlWhere.')';
        }

        $sql .= $sqlWhere.' GROUP BY rtfpr.`id_feature_price`';

        return Db::getInstance()->executeS($sql);
    }

    public function saveUpdateFeaturePrices($idFeaturePrice, $featurePriceRules)
    {
        $res = true;
        $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRule();
        if ($oldFeaturePriceRules = $objFeaturePriceRule->getRulesByIdFeaturePrice($idFeaturePrice)) {
            $oldFeaturePriceRules = array_column($oldFeaturePriceRules, 'id_feature_price_rule', 'id_feature_price_rule');
        }

        if ($featurePriceRules) {
            foreach ($featurePriceRules as $featurePriceRule) {
                if (isset($featurePriceRule['id']) && in_array($featurePriceRule['id'], $oldFeaturePriceRules)) {
                    $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRule($featurePriceRule['id']);
                    unset($oldFeaturePriceRules[$featurePriceRule['id']]);
                } else {
                    $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRule();
                }

                $objFeaturePriceRule->id_feature_price = $idFeaturePrice;
                $objFeaturePriceRule->date_from = $featurePriceRule['date_from'];
                $objFeaturePriceRule->date_to = $featurePriceRule['date_to'];
                $objFeaturePriceRule->date_selection_type = isset($featurePriceRule['date_selection_type']) ? $featurePriceRule['date_selection_type'] : HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE;
                $objFeaturePriceRule->special_days = json_encode(array());
                if ($objFeaturePriceRule->date_selection_type == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE
                    && $featurePriceRule['is_special_days_exists']
                ) {
                    $objFeaturePriceRule->is_special_days_exists = isset($featurePriceRule['is_special_days_exists']) ? $featurePriceRule['is_special_days_exists'] : 0;
                    if (isset($featurePriceRule['is_special_days_exists'])
                        && $featurePriceRule['is_special_days_exists']
                        && isset($featurePriceRule['special_days'])
                        && $featurePriceRule['special_days']
                    ) {
                        $objFeaturePriceRule->special_days = json_encode($featurePriceRule['special_days']);
                    }
                } else {
                    $objFeaturePriceRule->is_special_days_exists = 0;
                }

                $res &= $objFeaturePriceRule->save();
            }
        }

        if ($oldFeaturePriceRules) {
            $res &= $objFeaturePriceRule->deleteFeaturePriceRules($oldFeaturePriceRules);
        }

        return $res;
    }


    /**
     * [countFeaturePriceSpecialDays returns number of special days between a date range]
     * @param  [array] $specialDays [array containing special days to be counted]
     * @param  [date] $date_from   [start date of the date range]
     * @param  [date] $date_to     [end date of the date range]
     * @return [int]              [number of special days]
     */
    public static function countFeaturePriceSpecialDays($specialDays, $date_from, $date_to)
    {
        $specialDaysCount = 0;
        $date_from = date('Y-m-d', strtotime($date_from));
        $date_to = date('Y-m-d', strtotime($date_to));

        for($date = $date_from; $date < $date_to; $date = date('Y-m-d', strtotime('+1 day', strtotime($date)))) {
            if (in_array(Tools::strtolower(Date('D', $date)), $specialDays)) {
                $specialDaysCount++;
            }
        }
        return $specialDaysCount;
    }

    /**
     * [getRoomTypeTotalPrice Returns Total price of the room type according to supplied dates].
     *
     * @param [int]  $id_product [id of the room type]
     * @param [date] $date_from  [date from]
     * @param [date] $date_to    [date to]
     *
     * @return [float] [Returns Total price of the room type]
     */
    public static function getRoomTypeTotalPrice(
        $id_product,
        $date_from,
        $date_to,
        $occupancy = null,
        $id_group = 0,
        $id_cart = 0,
        $id_guest = 0,
        $id_room = 0,
        $with_auto_room_services = 1,
        $use_reduc = 1
    ) {
        $totalPrice = array();
        $totalPrice['total_price_tax_incl'] = 0;
        $totalPrice['total_price_tax_excl'] = 0;
        $featureImpactPriceTE = 0;
        $featureImpactPriceTI = 0;
        $productPriceTI = Product::getPriceStatic((int) $id_product, 1, 0, 6, null, 0, $use_reduc, 1, 0, null, null, null, $nothing, 1, 1, null, 1, 0, 0, $id_group);
        $productPriceTE = Product::getPriceStatic((int) $id_product, 0, 0, 6, null, 0, $use_reduc, 1, 0, null, null, null, $nothing, 1, 1, null, 1, 0, 0, $id_group);
        if ($productPriceTE) {
            $taxRate = (($productPriceTI-$productPriceTE)/$productPriceTE)*100;
        } else {
            $taxRate = 0;
        }

        if (is_array($occupancy) && count($occupancy)) {
            $quantity = count($occupancy);
        } else {
            $quantity = $occupancy;
        }

        // Initializations
        if (!$id_group) {
            $id_group = (int)Group::getCurrent()->id;
        }

        // if date_from and date_to are same then date_to will be the next date date of date_from
        if (strtotime($date_from) == strtotime($date_to)) {
            $date_to = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($date_from)));
        }
        $context = Context::getContext();
        $id_currency = Validate::isLoadedObject($context->currency) ? (int)$context->currency->id : (int)Configuration::get('PS_CURRENCY_DEFAULT');

        for($currentDate = date('Y-m-d', strtotime($date_from)); $currentDate < date('Y-m-d', strtotime($date_to)); $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)))) {
            if ($use_reduc && ($featurePrice = HotelCartBookingData::getProductFeaturePricePlanByDateByPriority(
                $id_product,
                $currentDate,
                $id_group,
                $id_cart,
                $id_guest,
                $id_room
            ))) {
                if ($featurePrice['impact_type'] == self::IMPACT_TYPE_PERCENTAGE) {
                    //percentage
                    $featureImpactPriceTE = $productPriceTE * ($featurePrice['impact_value'] / 100);
                    $featureImpactPriceTI = $productPriceTI * ($featurePrice['impact_value'] / 100);
                } else {
                    //Fixed Price
                    $taxPrice = ($featurePrice['impact_value']*$taxRate)/100;
                    $featureImpactPriceTE = Tools::convertPrice($featurePrice['impact_value'], $id_currency);
                    $featureImpactPriceTI = Tools::convertPrice($featurePrice['impact_value']+$taxPrice, $id_currency);
                }
                if ($featurePrice['impact_way'] == self::IMPACT_WAY_DECREASE) {
                    // Decrease
                    $priceWithFeatureTE = ($productPriceTE - $featureImpactPriceTE);
                    $priceWithFeatureTI = ($productPriceTI - $featureImpactPriceTI);
                } elseif ($featurePrice['impact_way'] == self::IMPACT_WAY_INCREASE) {
                    // Increase
                    $priceWithFeatureTE = ($productPriceTE + $featureImpactPriceTE);
                    $priceWithFeatureTI = ($productPriceTI + $featureImpactPriceTI);
                } else {
                    // Fix
                    $priceWithFeatureTE = $featureImpactPriceTE;
                    $priceWithFeatureTI = $featureImpactPriceTI;
                }
                if ($priceWithFeatureTI < 0) {
                    $priceWithFeatureTI = 0;
                    $priceWithFeatureTE = 0;
                }
                $totalPrice['total_price_tax_incl'] += $priceWithFeatureTI;
                $totalPrice['total_price_tax_excl'] += $priceWithFeatureTE;
            } else {
                $totalPrice['total_price_tax_incl'] += $productPriceTI;
                $totalPrice['total_price_tax_excl'] += $productPriceTE;
            }
        }
        Hook::exec('actionRoomTypeTotalPriceModifier',
            array(
                'total_prices' => &$totalPrice,
                'id_room_type' => $id_product,
                'id_room' => $id_room,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'id_currency' => $id_currency,
                'quantity' => $quantity,
                'id_cart' => $id_cart,
                'id_guest' => $id_guest,
                'id_group' => $id_group,
                'use_reduc' => $use_reduc,
                'tax_rate' => $taxRate,
                'occupancy' => $occupancy
            )
        );
        if ($with_auto_room_services) {
            if ($id_cart && $id_room) {
                $objHotelCartBookingData = new HotelCartBookingData();
                if ($roomHtlCartInfo = $objHotelCartBookingData->getRoomRowByIdProductIdRoomInDateRange(
                    $id_cart,
                    $id_product,
                    $date_from,
                    $date_to,
                    $id_room
                )) {
                    $objServiceProductCartDetail = new ServiceProductCartDetail();
                    if ($roomServicesServices = $objServiceProductCartDetail->getServiceProductsInCart(
                        $id_cart,
                        [],
                        null,
                        $roomHtlCartInfo['id'],
                        null,
                        null,
                        null,
                        null,
                        0,
                        1,
                        Product::PRICE_ADDITION_TYPE_WITH_ROOM
                    )) {
                        $selectedServices = array_shift($roomServicesServices);
                        $totalPrice['total_price_tax_incl'] += $selectedServices['total_price_tax_incl'];
                        $totalPrice['total_price_tax_excl'] += $selectedServices['total_price_tax_excl'];
                    }
                }

            } else {
                if ($servicesWithTax = RoomTypeServiceProduct::getAutoAddServices(
                    $id_product,
                    $date_from,
                    $date_to,
                    Product::PRICE_ADDITION_TYPE_WITH_ROOM,
                    true,
                    $use_reduc
                )) {
                    foreach($servicesWithTax as $service) {
                        $totalPrice['total_price_tax_incl'] += Tools::processPriceRounding($service['price']);
                    }
                }
                if ($servicesWithoutTax = RoomTypeServiceProduct::getAutoAddServices(
                    $id_product,
                    $date_from,
                    $date_to,
                    Product::PRICE_ADDITION_TYPE_WITH_ROOM,
                    false,
                    $use_reduc
                )) {
                    foreach($servicesWithoutTax as $service) {
                        $totalPrice['total_price_tax_excl'] += Tools::processPriceRounding($service['price']);
                    }
                }
            }
        }

        if (!$quantity) {
            $quantity = 1;
        }
        $totalPrice['total_price_tax_incl'] = Tools::processPriceRounding($totalPrice['total_price_tax_incl'], $quantity);
        $totalPrice['total_price_tax_excl'] = Tools::processPriceRounding($totalPrice['total_price_tax_excl'], $quantity);

        return $totalPrice;
    }

    /**
     * [getRoomTypeFeaturePricePerDay returns per day feature price od the Room Type]
     * @param  [int] $id_product [id of the product]
     * @param  [date] $date_from  [start date]
     * @param  [date] $date_to    [end date]
     * @return [float] [returns per day feature price of the Room Type]
     */
    public static function getRoomTypeFeaturePricesPerDay(
        $id_product,
        $date_from,
        $date_to,
        $use_tax = true,
        $id_group = 0,
        $id_cart = 0,
        $id_guest = 0,
        $id_room = 0,
        $with_auto_room_services = 1,
        $use_reduc = 1,
        $occupancy = array()
    ) {
        $dateFrom = date('Y-m-d H:i:s', strtotime($date_from));
        $dateTo = date('Y-m-d H:i:s', strtotime($date_to));
        $totalDurationPrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
            $id_product,
            $dateFrom,
            $dateTo,
            $occupancy,
            $id_group,
            $id_cart,
            $id_guest,
            $id_room,
            $with_auto_room_services,
            $use_reduc
        );

        $totalDurationPriceTI = $totalDurationPrice['total_price_tax_incl'];
        $totalDurationPriceTE = $totalDurationPrice['total_price_tax_excl'];
        $numDaysInDuration = HotelHelper::getNumberOfDays($dateFrom, $dateTo);
        if ($use_tax) {
            $pricePerDay = $totalDurationPriceTI/$numDaysInDuration;
        } else {
            $pricePerDay = $totalDurationPriceTE/$numDaysInDuration;
        }
        return $pricePerDay;
    }

    /**
     * [getFeaturePricesbyIdProduct returns all feature prices by product]
     * @param  [int] $id_product [id of the product]
     * @return [array] [returns all feature prices by product]
     */
    public function getFeaturePricesbyIdProduct($id_product, $id_cart = 0, $id_guest = 0, $id_room = 0)
    {
        $idLang = Context::getContext()->language->id;
        return Db::getInstance()->executeS(
            'SELECT hrfp.*, hrfpr.`date_from`, hrfpr.`date_to`, hrfpr.`date_selection_type`, hrfpr.`is_special_days_exists`, hrfpr.`special_days`, hrfpl.`feature_price_name`
            FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` hrfp
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_lang` hrfpl
            ON(hrfp.`id_feature_price` = hrfpl.`id_feature_price` AND hrfpl.`id_lang` = '.(int)$idLang.')
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_rule` hrfpr
            ON (hrfpr.`id_feature_price` = hrfp.`id_feature_price`)
            WHERE `id_product` = '.(int)$id_product.' AND `id_cart` = '.(int)$id_cart.' AND `id_guest` = '.(int)$id_guest.' AND `id_room` = '.(int)$id_room
        );
    }

    /**
     * @deprecated since 1.6.1 use deleteFeaturePrices() instead
    */
    public function deleteFeaturePriceByIdProduct($idProduct)
    {
        if (!$idProduct) {
            return false;
        }
        return HotelRoomTypeFeaturePricing::deleteFeaturePrices(false, $idProduct);
    }

    /**
     * @deprecated since 1.6.1 use deleteFeaturePrices() instead
    */
    public static function deleteByIdCart(
        $id_cart,
        $id_product = false,
        $id_room = false,
        $date_from = false,
        $date_to = false
    ) {
        return HotelRoomTypeFeaturePricing::deleteFeaturePrices(
            $id_cart,
            $id_product,
            $id_room,
            $date_from,
            $date_to
        );
    }

    public static function deleteFeaturePrices(
        $id_cart = false,
        $id_product = false,
        $id_room = false,
        $date_from = false,
        $date_to = false
    ) {
        if ($date_from) {
            $date_from = date('Y-m-d', strtotime($date_from));
        }

        if ($date_to) {
            $date_to = date('Y-m-d', strtotime($date_to));
        }

        $idfeaturePrices = Db::getInstance()->executeS(
            'SELECT hrfp.`id_feature_price`  FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` hrfp
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_rule` hrfpr
            ON (hrfpr.`id_feature_price` = hrfp.`id_feature_price`)
            WHERE 1'.
            ($id_cart ? ' AND hrfp.`id_cart` = '.(int) $id_cart : '').
            ($id_product ? ' AND hrfp.`id_product` = '.(int) $id_product : '').
            ($id_room ? ' AND hrfp.`id_room` = '.(int) $id_room : '').
            ($date_from ? ' AND hrfpr.`date_from` = "'.pSQL($date_from) .'"' : '').
            ($date_to ? ' AND hrfpr.`date_to` = "'.pSQL($date_to) .'"' : '')
        );
        $res = true;
        foreach ($idfeaturePrices as $featurePrice) {
            $objHotelRoomTypeFeaturePricing = new HotelRoomTypeFeaturePricing((int)$featurePrice['id_feature_price']);
            $res = $res && $objHotelRoomTypeFeaturePricing->delete();
        }
        return $res;
    }

    /**
     * Update customer groups associated to the object
     * @param array $groups groups
     */
    public function updateGroup($groups)
    {
        if ($groups && !empty($groups)) {
            $this->cleanGroups();
            $this->addGroups($groups);
        }
    }

    /**
     * Deletes groups entries in the table. Send id_group if you want to delete entries by group i.e. when group deletes
     * @param integer $idGroup
     * @return void
     */
    public function cleanGroups($idGroup = 0)
    {
        if ($idGroup) {
            $condition = 'id_group = '.(int)$idGroup;
        } else {
            $condition = 'id_feature_price = '.(int)$this->id;
        }

    	return Db::getInstance()->delete('htl_room_type_feature_pricing_group', $condition);
    }

    /**
     * Add customer groups associated to the object
     * @param array $groups groups
     */
    public function addGroups($groups)
    {
        if ($groups && !empty($groups)) {
            foreach ($groups as $group) {
                $row = array('id_feature_price' => (int)$this->id, 'id_group' => (int)$group);
                Db::getInstance()->insert('htl_room_type_feature_pricing_group', $row, false, true, Db::INSERT_IGNORE);
            }
        }
    }

    public function getGroups($idFeaturePrice)
    {
        $groups = array();
        if ($results = Db::getInstance()->executeS(
            ' SELECT `id_group` FROM '._DB_PREFIX_.'htl_room_type_feature_pricing_group
            WHERE `id_feature_price` = '.(int)$idFeaturePrice
        )) {
            foreach ($results as $group) {
                $groups[] = (int)$group['id_group'];
            }
        }
        return $groups;
    }

    // Webservice:: get groups in the feature price
    public function getWsGroups()
    {
        return Db::getInstance()->executeS('
			SELECT fg.`id_group` as id
			FROM '._DB_PREFIX_.'htl_room_type_feature_pricing_group fg
			'.Shop::addSqlAssociation('group', 'fg').'
			WHERE fg.`id_feature_price` = '.(int)$this->id
        );
    }

    // Webservice:: set groups in the feature price
    public function setWsGroups($result)
    {
        $groups = array();
        foreach ($result as $row) {
            $groups[] = $row['id'];
        }
        $this->cleanGroups();
        $this->addGroups($groups);
        return true;
    }

    public function getWsAdvancePriceRule()
    {
        return Db::getInstance()->executeS(
            'SELECT *, id_feature_price_rule AS `id` FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing_rule`
            WHERE `id_feature_price` ='.(int)$this->id.' ORDER BY `id_feature_price` ASC'
        );

    }

    public function setWsAdvancePriceRule($priceRules)
    {
        foreach ($priceRules as $ruleKey => $priceRule) {
            if ($priceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE
                && $priceRule['is_special_days_exists']
            ) {
                $specialDays = json_decode($priceRule['special_days'], true);
                $priceRules[$ruleKey]['special_days'] = $specialDays;
            }
        }

        return $this->saveUpdateFeaturePrices($this->id, $priceRules);
    }

    public function validateExistingFeaturePrice(
        $roomTypeId,
        $group,
        $idFeaturePrice,
        $featurePriceRules
    ) {
        $duplicateRules = array();
        if ($oldPriceRules = $this->getExistingFeaturePriceRules(
            $roomTypeId,
            $group,
            $idFeaturePrice,
            $featurePriceRules
        )) {
            foreach ($oldPriceRules as $oldPriceRule) {
                foreach ($featurePriceRules as $ruleKey => $rule) {
                    if ($rule['date_selection_type'] == $oldPriceRule['date_selection_type']) {
                        if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC) {
                            if (strtotime($rule['date_from']) != strtotime($oldPriceRule['date_from'])) {
                                continue;
                            } else {
                                $duplicateRules[] = $ruleKey;
                            }
                        } else if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
                            if (((strtotime($oldPriceRule['date_from']) < strtotime($rule['date_from'])) && (strtotime($oldPriceRule['date_to']) <= strtotime($rule['date_from']))) || ((strtotime($oldPriceRule['date_from']) > strtotime($rule['date_from'])) && (strtotime($oldPriceRule['date_from']) >= strtotime($rule['date_to'])))) {
                                continue;
                            } else {
                                if ($oldPriceRule['is_special_days_exists'] && $rule['is_special_days_exists']) {
                                    if (!empty($oldPriceRule['special_days']) && !empty($rule['special_days'])) {
                                        $existingDays = json_decode($oldPriceRule['special_days'], true);
                                        if (array_intersect($existingDays, $rule['special_days'])) {
                                            $duplicateRules[] = $ruleKey;
                                        }
                                    }
                                } else {
                                    $duplicateRules[] = $ruleKey;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $duplicateRules;
    }

    public function validateFields($die = true, $error_return = false)
    {
        if (isset($this->webservice_validation) && $this->webservice_validation) {
            $priceRules = array();
            $idGroups = array();
            if (isset($this->associations)) {
                foreach ($this->associations->children() as $association) {
                    if ($association->getName() == 'price_rules') {
                        $assocItems = $association->children();
                        foreach ($assocItems as $assocItem) {
                            /** @var SimpleXMLElement $assocItem */
                            $fields = $assocItem->children();
                            $entry = array();
                            foreach ($fields as $fieldName => $fieldValue) {
                                $entry[$fieldName] = (string)$fieldValue;
                            }

                            $priceRules[] = $entry;
                        }
                    } else if ($association->getName() == 'groups') {
                        $assocItems = $association->children();
                        foreach ($assocItems as $assocItem) {
                            /** @var SimpleXMLElement $assocItem */
                            $fields = $assocItem->children();
                            $entry = array();
                            foreach ($fields as $fieldName => $fieldValue) {
                                $entry[$fieldName] = (string)$fieldValue;
                            }

                            $idGroups[] = $entry;
                        }
                    }
                }
            }
            if ($idGroups) {
                $idGroups = array_column($idGroups, 'id');
            }

            if ($priceRules) {
                $hasError = false;
                $weekDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                // check for invalid special days
                foreach ($priceRules as $ruleKey => $priceRule) {
                    if ($priceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
                        if ($priceRule['is_special_days_exists']) {
                            $specialDays = json_decode($priceRule['special_days'], true);
                            $priceRules[$ruleKey]['special_days'] = $specialDays;
                            if (is_array($specialDays) && $specialDays) {
                                if (count(array_diff($specialDays, $weekDays))) {
                                    $message = Tools::displayError('Invalid special days. format must match with : ["mon", "tue", "wed", "thu", "fri", "sat", "sun"].', true);
                                    $hasError = true;
                                    break;
                                }
                            } else {
                                $message = Tools::displayError('Invalid special days. format must match with : ["mon", "tue", "wed", "thu", "fri", "sat", "sun"].', true);
                                $hasError = true;
                                break;
                            }
                        }
                    }
                }

                if (!$hasError) {
                    // check for conflicting dates in the rules.
                    foreach ($priceRules as $priceRuleKey => $priceRule) {
                        foreach ($priceRules as $ruleKey => $rule) {
                            if ($priceRuleKey != $ruleKey) {
                                if ($rule['date_selection_type'] == $priceRule['date_selection_type']) {
                                    if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC) {
                                        if (strtotime($rule['date_from']) != strtotime($priceRule['date_from'])) {
                                            continue;
                                        } else {
                                            $message = Tools::displayError('You can not add conflicting dates.', true);
                                            $hasError = true;
                                            break;
                                        }
                                    } else if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
                                        if (((strtotime($priceRule['date_from']) < strtotime($rule['date_from'])) && (strtotime($priceRule['date_to']) <= strtotime($rule['date_from']))) || ((strtotime($priceRule['date_from']) > strtotime($rule['date_from'])) && (strtotime($priceRule['date_from']) >= strtotime($rule['date_to'])))) {
                                            continue;
                                        } else {
                                            if ($priceRule['is_special_days_exists'] && $rule['is_special_days_exists']) {
                                                if (!empty($priceRule['special_days']) && !empty($rule['special_days'])) {
                                                    if (array_intersect($priceRule['special_days'], $rule['special_days'])) {
                                                        $message = Tools::displayError('You can not add conflicting days for similar date ranges.', true);
                                                        $hasError = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $message = Tools::displayError('You can not add conflicting date ranges.', true);
                                                $hasError = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                        }
                    }
                }

                if (!$hasError) {
                    $hasDuplicate = $this->validateExistingFeaturePrice(
                        $this->id_product,
                        $idGroups,
                        $this->id,
                        $priceRules
                    );

                    if ($hasDuplicate) {
                        $message = Tools::displayError('An advanced price rule already exists with overlapping conditions.', false);
                    }
                }
            } else {
                $message = Tools::displayError('Price rules are required.', false);
            }

            if (isset($message) && $message != '') {
                if ($die) {
                    throw new PrestaShopException($message);
                }

                return $error_return ? $message : false;
            }
        }

        return parent::validateFields($die, $error_return);
    }

    public static function createRoomTypeFeaturePrice($params)
    {
        $context = Context::getContext();
        $featurePriceName = array();
        foreach (Language::getIDs(true) as $idLang) {
            if (isset($params['name']) && $params['name']) {
                $featurePriceName[$idLang] = $params['name'];
            } else {
                $featurePriceName[$idLang] = 'Auto-generated';
            }
        }

        if (isset($params['id']) && $params['id']) {
            $objFeaturePricing = new HotelRoomTypeFeaturePricing($params['id']);
        } else {
            $objFeaturePricing = new HotelRoomTypeFeaturePricing();
        }

        $objFeaturePricing->id_product = (int) $params['id_product'];
        $objFeaturePricing->id_cart = (int) isset($params['id_cart']) ? $params['id_cart'] : 0;
        $objFeaturePricing->id_guest = (int) isset($params['id_guest']) ? $params['id_guest'] : 0;
        $objFeaturePricing->id_room = (int) isset($params['id_room']) ? $params['id_room'] : 0;
        $objFeaturePricing->feature_price_name = $featurePriceName;
        $objFeaturePricing->impact_way = isset($params['impact_way']) ? $params['impact_way'] : HotelRoomTypeFeaturePricing::IMPACT_WAY_FIX_PRICE;
        $objFeaturePricing->impact_type = isset($params['impact_type']) ? $params['impact_type'] : HotelRoomTypeFeaturePricing::IMPACT_TYPE_FIXED_PRICE;
        $objFeaturePricing->impact_value = isset($params['price']) ? $params['price'] : 0;
        $objFeaturePricing->active = isset($params['active']) ? $params['active'] : 1;
        $objFeaturePricing->groupBox = !empty($params['groupBox']) ?  $params['groupBox'] : array_column(Group::getGroups($context->language->id), 'id_group');
        if ($objFeaturePricing->add()) {
            $objFeaturePricing->saveUpdateFeaturePrices($objFeaturePricing->id, $params['price_rules']);
        }

        return $objFeaturePricing->id;
    }
}
