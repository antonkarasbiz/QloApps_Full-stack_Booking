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

class AdminHotelFeaturePricesSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'htl_room_type_feature_pricing';
        $this->className = 'HotelRoomTypeFeaturePricing';
        $this->bootstrap = true;
        $this->identifier  = 'id_feature_price';
        $this->context = Context::getContext();

        // START send access query information to the admin controller
        $this->access_select = ' SELECT a.`id_feature_price` FROM '._DB_PREFIX_.'htl_room_type_feature_pricing a';
        $this->access_join = ' INNER JOIN '._DB_PREFIX_.'htl_room_type hrt ON (hrt.id_product = a.id_product)';
        if ($acsHtls = HotelBranchInformation::getProfileAccessedHotels($this->context->employee->id_profile, 1, 1)) {
            $this->access_where = ' WHERE hrt.id_hotel IN ('.implode(',', $acsHtls).')';
        }

        parent::__construct();
        $this->_new_list_header_design = true;

        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = a.`id_product` AND pl.`id_lang`='.(int) $this->context->language->id.')';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_lang` fpl ON (a.id_feature_price = fpl.id_feature_price AND fpl.`id_lang` = '.(int) $this->context->language->id.')';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'htl_room_type` hrt ON (hrt.`id_product` = a.`id_product`)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl ON (hbl.`id` = hrt.`id_hotel` AND hbl.`id_lang`='.(int) $this->context->language->id.')';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_group` hrtfpg ON (hrtfpg.`id_feature_price` = a.`id_feature_price`)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'group_lang` gl ON (gl.`id_group` = hrtfpg.`id_group` AND gl.`id_lang` = '.(int) $this->context->language->id.')';

        $this->_select .= ' fpl.`feature_price_name` as ftr_price_name, CONCAT(pl.`name`, " (#", a.`id_product`, ")") as product_name, hbl.`hotel_name`, IF(a.impact_type='.(int) HotelRoomTypeFeaturePricing::IMPACT_TYPE_PERCENTAGE.', CONCAT(round(a.impact_value, 2), " ", "%"), a.impact_value) AS impact_value';
        $this->_select .= ' ,IF(a.impact_type='.(int) HotelRoomTypeFeaturePricing::IMPACT_TYPE_PERCENTAGE.', \''.$this->l('Percentage').'\', \''.$this->l('Fixed Amount').'\')
        AS impact_type, count(hrtfpg.`id_feature_price`) AS group_access_count, group_concat(gl.`name` separator ", ") as group_names';
        $this->_select .= ', CASE
            WHEN a.`impact_way` = '.(int) HotelRoomTypeFeaturePricing::IMPACT_WAY_DECREASE.' THEN \''.$this->l('Decrease').'\'
            WHEN a.`impact_way` = '.(int) HotelRoomTypeFeaturePricing::IMPACT_WAY_INCREASE.' THEN \''.$this->l('Increase').'\'
            WHEN a.`impact_way` = '.(int) HotelRoomTypeFeaturePricing::IMPACT_WAY_FIX_PRICE.' THEN \''.$this->l('Fix').'\'
        END AS `impact_way`';

        $this->_group = 'GROUP BY a.`id_feature_price`';

        $this->_where = ' AND a.`id_cart` = 0 AND a.`id_guest` = 0 AND a.`id_room` = 0';

        $impactWays = array(
            HotelRoomTypeFeaturePricing::IMPACT_WAY_DECREASE => $this->l('Decrease'),
            HotelRoomTypeFeaturePricing::IMPACT_WAY_INCREASE => $this->l('Increase'),
            HotelRoomTypeFeaturePricing::IMPACT_WAY_FIX_PRICE => $this->l('Fix')
        );

        $impactTypes = array(
            HotelRoomTypeFeaturePricing::IMPACT_TYPE_PERCENTAGE => $this->l('Percentage'),
            HotelRoomTypeFeaturePricing::IMPACT_TYPE_FIXED_PRICE => $this->l('Fixed Price')
        );

        $priorities = Configuration::get('HTL_FEATURE_PRICING_PRIORITY');
        $this->context->smarty->assign('featurePricePriority', explode(';', $priorities));
        $this->fields_options = array('feature_price_priority' => array());
        $this->fields_list = array(
            'id_feature_price' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
            ),
            'ftr_price_name' => array(
                'title' => $this->l('Rule Name'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'product_name' => array(
                'title' => $this->l('Room Type'),
                'align' => 'center',
                'havingFilter' => true,
                'callback' => 'getRoomTypeLink',
            ),
            'hotel_name' => array(
                'title' => $this->l('Hotel'),
                'align' => 'center',
            ),
            'impact_way' => array(
                'title' => $this->l('Impact Way'),
                'align' => 'center',
                'type' => 'select',
                'list' => $impactWays,
                'filter_key' => 'a!impact_way',
            ),
            'impact_type' => array(
                'title' => $this->l('Impact Type'),
                'align' => 'center',
                'type' => 'select',
                'list' => $impactTypes,
                'filter_key' => 'a!impact_type',
            ),
            'impact_value' => array(
                'title' => $this->l('Impact Value'),
                'align' => 'center',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
            'group_access_count' => array(
                'align' => 'center',
                'title' => $this->l('Group Access'),
                'type' => 'bool',
                'callback' => 'setGroupAccessLabel',
                'search' => false,
            ),
            'active' => array(
                'align' => 'center',
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
            ),
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );

        $this->list_no_link = true;
    }

    public function getDateToValue($dateTo, $row)
    {
        if ($row['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
            return date($this->context->language->date_format_lite, strtotime($dateTo));
        } else {
            return '<span class="badge badge-success">'.$this->l('Specific date').'</span>';
        }
    }

    public function getRoomTypeLink($productName, $row)
    {
        $displayData = '';
        if ($row['id_product']) {
            $displayData .= '<a target="_blank" href="'.$this->context->link->getAdminLink('AdminProducts').
                '&id_product='.$row['id_product'].'&updateproduct">'.$productName.'</a>';
        }
        return $displayData;
    }

    //A callback function for setting currency sign with amount
    public static function setOrderCurrency($echo, $row)
    {
        $currency_default = Configuration::get('PS_CURRENCY_DEFAULT');
        return Tools::displayPrice($echo, (int)$currency_default);
    }

    public function setGroupAccessLabel($echo, $row)
    {
        $this->context->smarty->assign('row' ,$row);
        $tpl_path = 'hotelreservationsystem/views/templates/admin/hotel_feature_prices_settings/group_access_label.tpl';
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$tpl_path);
    }

    public function processStatus()
    {
        $objFeaturePricing = $this->loadObject();
        $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRestriction();
        if (!$objFeaturePricing->active) {
            if ($this->object->validateExistingFeaturePrice(
                $objFeaturePricing->id_product,
                $objFeaturePricing->getGroups($objFeaturePricing->id),
                $objFeaturePricing->id,
                $objFeaturePriceRule->getRestrictionsByIdFeaturePrice($objFeaturePricing->id)
            )) {
                $this->errors[] = $this->l('An advanced price rule already exists with overlapping conditions. Please update the existing rule to activate this one.');
                return ;
            }
       }

        return parent::processStatus();

    }

    public function initToolbar()
    {
        parent::initToolbar();
        if (empty($this->display) || $this->display == 'list')  {
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new rule'),
                'imgclass' => 'new'
            );
        }
        $this->toolbar_btn = array();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            return false;
        }

        $smartyVars = array();
        $objCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currencySign = $objCurrency->sign;
        $dateFrom = date('Y-m-d');
        $dateTo = date('Y-m-d', strtotime($dateFrom) + 86400);
        $currentLangId = $this->default_form_language ? $this->default_form_language : Configuration::get('PS_LANG_DEFAULT');

        $smartyVars['languages'] = Language::getLanguages(false);
        $smartyVars['currentLang'] = Language::getLanguage((int) $currentLangId);

        if ($this->display == 'edit'  && $this->object->id) {
            $idFeaturePrice = Tools::getValue('id_feature_price');
            if (Validate::isLoadedObject(
                $objFeaturePrice = new HotelRoomTypeFeaturePricing($idFeaturePrice)
            )) {
                if ($objFeaturePrice->id_product) {
                    $product = new Product($objFeaturePrice->id_product, false, Configuration::get('PS_LANG_DEFAULT'));
                    $smartyVars['productName'] =  $product->name;
                }
            }

            $smartyVars['objFeaturePrice'] = $objFeaturePrice;
            $smartyVars['edit'] = 1;

            $objFeaturePriceRule = new HotelRoomTypeFeaturePricingRestriction();
            if ($advancePriceRules = $objFeaturePriceRule->getRestrictionsByIdFeaturePrice($objFeaturePrice->id)) {
                foreach ($advancePriceRules as $priceRuleKey => $advancePriceRule) {
                    if ($advancePriceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC) {
                        $advancePriceRules[$priceRuleKey]['specific_date'] = $advancePriceRule['date_from'];
                        unset($advancePriceRules[$priceRuleKey]['date_from']);
                        unset($advancePriceRules[$priceRuleKey]['date_to']);
                    } else if ($advancePriceRule['is_special_days_exists']) {
                        $advancePriceRules[$priceRuleKey]['special_days'] = json_decode($advancePriceRule['special_days'], true);
                    }
                }
            }

            $smartyVars['advancePriceRules'] = $advancePriceRules;
            $smartyVars['feature_price_groups'] = $objFeaturePrice->getGroups($idFeaturePrice);
        } else {
            $tree = new HelperTree('hotels-tree');
            if ($treeData = HotelHelper::generateTreeData([
                'rootNode' => HotelHelper::NODE_HOTEL,
                'leafNode' => HotelHelper::NODE_ROOM_TYPE,
                'selectedElements' => array(
                    'room_type' => Tools::getValue('room_type_box', array())
                )
            ])) {
                foreach ($treeData as $idHotel => $data) {
                    if (!isset($data['children']) || empty($data['children'])) {
                        unset($treeData[$idHotel]);
                    }
                }
            }

            $tree->setData($treeData)
                ->setUseCheckBox(true)
                ->setAutoSelectChildren(true)
                ->setUseBulkActions(true)
                ->setUseSearch(true);
            $smartyVars['hotel_tree'] = $tree->render();
        }

        $smartyVars['week_days'] = $this->getWeekDays();
        $smartyVars['defaultcurrency_sign'] = $currencySign;
        $smartyVars['date_from'] = $dateFrom;
        $smartyVars['date_to'] = $dateTo;
        $smartyVars['groups'] = Group::getGroups($this->context->language->id);

        $this->context->smarty->assign($smartyVars);
        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    public function getWeekDays()
    {
        return array(
            'mon' => $this->l('Mon'),
            'tue' => $this->l('Tue'),
            'wed' => $this->l('Wed'),
            'thu' => $this->l('Thu'),
            'fri' => $this->l('Fri'),
            'sat' => $this->l('Sat'),
            'sun' => $this->l('Sun')
        );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddFeaturePricePriority')) {
            $priority = Tools::getValue('featurePricePriority');
            $uniquePriorities = array_unique($priority);
            if (count($priority) == count($uniquePriorities)) {
                $priorityConfig = implode(';', $priority);
                if (Configuration::updateValue('HTL_FEATURE_PRICING_PRIORITY', $priorityConfig)) {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                } else {
                    $this->errors[] = $this->l('Some error occurred while updating advanced price rule priorities.');
                }
            } else {
                $this->errors[] = $this->l('Duplicate values selected for advanced price rule priorities.');
            }
        } else {
            parent::postProcess();
        }
    }

    public function processSave()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        $idFeaturePrice = Tools::getValue('id_feature_price');
        if (!isset($idFeaturePrice) || !$idFeaturePrice) {
            $idFeaturePrice = 0;
        }
        $enableFeaturePrice = Tools::getValue('enable_feature_price');
        $roomTypeId = Tools::getValue('room_type_id');
        $priceImpactWay = Tools::getValue('price_impact_way');
        $priceImpactType = Tools::getValue('price_impact_type');
        $impactValue = Tools::getValue('impact_value');
        $groups = Tools::getValue('groupBox');
        $defaultLangId = Configuration::get('PS_LANG_DEFAULT');
        $createMultiple = Tools::getValue('create_multiple');

        $priceRules = Tools::getValue('advance_price_rule', array());
        $objFeaturePricing = new HotelRoomTypeFeaturePricing();
        if ($priceImpactWay == HotelRoomTypeFeaturePricing::IMPACT_WAY_FIX_PRICE) {
            $priceImpactType = HotelRoomTypeFeaturePricing::IMPACT_TYPE_FIXED_PRICE;
        }

        $languages = Language::getLanguages(false);
        $objDefaultLang = new Language($defaultLangId);
        if (!($priceRuleNameDefault = Tools::getValue('feature_price_name_'.$defaultLangId))) {
            $this->errors[] = sprintf($this->l('Advanced price rule name is required at least in %s'), $objDefaultLang->name);
        } else {
            if (preg_match('{room_type_name}', $priceRuleNameDefault)) {
                $priceRuleNameDefault = str_replace('{room_type_name}', '%s', $priceRuleNameDefault);
            }
        }

        $toUpdatePriceRuleName = false;
        $validateRules = call_user_func(
            array('HotelRoomTypeFeaturePricing', 'getValidationRules'),
            'HotelRoomTypeFeaturePricing'
        );
        foreach ($languages as $lang) {
            $priceRuleName = Tools::getValue('feature_price_name_'.$lang['id_lang']);
            if (preg_match('{room_type_name}', $priceRuleName)) {
                $priceRuleName = str_replace('{room_type_name}', '%s', $priceRuleName);
                $toUpdatePriceRuleName = true;
            }
            if (!Validate::isCatalogName($priceRuleName)) {
                $this->errors[] = $this->l('Advanced price rule name is invalid in ').$lang['name'];
            } elseif (Tools::strlen($priceRuleName) > $validateRules['sizeLang']['feature_price_name']) {
                $this->errors[] = sprintf(
                    $this->l('Advanced price rule Name field is too long (%d chars max) in ').$lang['name'],
                    $validateRules['sizeLang']['feature_price_name'],
                );
            }
        }

        $featurePricingName = array();
        $roomTypeIds = array();
        if (!$createMultiple) {
            if ($roomTypeId) {
                $roomTypeIds = array($roomTypeId);
            } else {
                $this->errors[] = $this->l('Room type is not selected. Please try again.');
            }
        } else if (!$roomTypeIds = Tools::getValue('room_type_box')) {
            $this->errors[] = $this->l('Please select at least one room type for creating multiple price rules.');
        }

        if ($roomTypeIds && !$this->errors) {
            foreach ($roomTypeIds as $idRoomType) {
                $objProduct = new Product((int) $idRoomType);
                foreach ($languages as $lang) {
                    $priceRuleName = Tools::getValue('feature_price_name_'.$lang['id_lang']);
                    if ($priceRuleName && $toUpdatePriceRuleName) {
                        if (preg_match('{room_type_name}', $priceRuleName)) {
                            $priceRuleName = str_replace('{room_type_name}', $objProduct->name[$lang['id_lang']], $priceRuleName);
                        }
                        if (Tools::strlen($priceRuleName) > $validateRules['sizeLang']['feature_price_name']) {
                            $this->errors[] = sprintf(
                                $this->l('Advanced price rule Name field is too long (%d chars max) for "%s" room type in ').$lang['name'],
                                $validateRules['sizeLang']['feature_price_name'],
                                $objProduct->name[$lang['id_lang']]
                            );
                        }
                    }

                    if ($priceRuleName) {
                        $featurePricingName[$idRoomType][$lang['id_lang']] = $priceRuleName;
                    } else {
                        $featurePricingName[$idRoomType][$lang['id_lang']] = $priceRuleNameDefault;
                    }
                }
            }
        }

        $rowsToHighlight = [];
        if (!$priceRules) {
            $this->errors[] = $this->l('Atleast one restriction is required while creating advance price rule.');
        } else {
            foreach ($priceRules as $priceRuleKey => $priceRule) {
                if (!$priceRule['is_special_days_exists']) {
                    unset($priceRules[$priceRuleKey]['special_days']);
                }
                if ($priceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC) {
                    if ($priceRule['specific_date']) {
                        $priceRules[$priceRuleKey]['date_from'] = $priceRule['specific_date'];
                        $priceRules[$priceRuleKey]['date_to'] = date('Y-m-d', strtotime("+1 day", strtotime($priceRule['specific_date'])));
                    } else {
                        $rowsToHighlight[] = $priceRuleKey;
                        $this->errors = $this->l('Specific Date is missing for price rule restriction.');
                    }
                } else if ($priceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
                    if ($priceRule['date_from'] == '') {
                        $rowsToHighlight[] = $priceRuleKey;
                        $this->errors[] = $this->l('Dates are invalid in price rule restriction.');
                    } else if ($priceRule['date_to'] == '') {
                        $this->errors[] = $this->l('Dates are invalid in price rule restriction');
                        $rowsToHighlight[] = $priceRuleKey;
                    }

                    $dateFrom = date('Y-m-d', strtotime($priceRule['date_from']));
                    $dateTo = date('Y-m-d', strtotime($priceRule['date_to']));
                    if (!Validate::isDate($dateFrom)) {
                        $this->errors[] = $this->l('Dates are invalid in price rule restriction');
                        $rowsToHighlight[] = $priceRuleKey;
                    }
                    if (!Validate::isDate($dateTo)) {
                        $this->errors[] = $this->l('Dates are invalid in price rule restriction');
                    }
                    if ($dateTo < $dateFrom) {
                        $this->errors[] = $this->l('Dates are invalid in price rule restriction');
                    }
                    if ($priceRule['is_special_days_exists']) {
                        if (!isset($priceRule['special_days']) || !$priceRule['special_days']) {
                            $this->errors[] = $this->l('Special days are missing for price rule having restrict to week days');
                            $rowsToHighlight[] = $priceRuleKey;
                        }
                    }
                    unset($priceRules[$priceRuleKey]['specific_date']);
                } else {
                    unset($priceRules[$priceRuleKey]);
                }

                if ($priceRule['is_special_days_exists'] && empty($priceRule['special_days'])) {
                    $rowsToHighlight[] = $priceRuleKey;
                    $this->errors[] = $this->l('Please select at least one day for week days restriction.');
                }

                foreach ($priceRules as $ruleKey => $rule) {
                    if ($priceRuleKey != $ruleKey) {
                        if ($rule['date_selection_type'] == $priceRule['date_selection_type']) {
                            if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC) {
                                if (strtotime($rule['specific_date']) != strtotime($priceRule['specific_date'])) {
                                    continue;
                                } else {
                                    $this->errors[] = $this->l('Some dates are conflicting. Please check and reselect the dates.');
                                    $rowsToHighlight[] = $priceRuleKey;
                                }
                            } else if ($rule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE) {
                                if (((strtotime($priceRule['date_from']) < strtotime($rule['date_from'])) && (strtotime($priceRule['date_to']) <= strtotime($rule['date_from']))) || ((strtotime($priceRule['date_from']) > strtotime($rule['date_from'])) && (strtotime($priceRule['date_from']) >= strtotime($rule['date_to'])))) {
                                    continue;
                                } else {
                                    if ($priceRule['is_special_days_exists'] && $rule['is_special_days_exists']) {
                                        if (!empty($priceRule['special_days']) && !empty($rule['special_days'])) {
                                            if (array_intersect($priceRule['special_days'], $rule['special_days'])) {
                                                $this->errors[] = $this->l('Some days are conflicting. Please check and update the special days.');
                                                $rowsToHighlight[] = $priceRuleKey;
                                            }
                                        }
                                    } else {
                                        $this->errors[] = $this->l('Some dates are conflicting. Please check and reselect the date ranges.');
                                        $rowsToHighlight[] = $priceRuleKey;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$impactValue) {
            $this->errors[] = $this->l('Please enter a valid impact value.');
        } else if (!Validate::isPrice($impactValue)) {
            $this->errors[] = $this->l('Invalid value of impact value.');
        }

        if (!(bool)$groups) {
            $this->errors[] = $this->l('Please select at least one group for the group access');
        }

        $duplicatePriceRuleKey = 0;
        if (empty($this->errors) && $enableFeaturePrice) {
            $objFeaturePricing = new HotelRoomTypeFeaturePricing();
            foreach ($roomTypeIds as $idRoomType) {
                $duplicatePriceRuleKey = $this->object->validateExistingFeaturePrice(
                    $idRoomType,
                    $groups,
                    $idFeaturePrice,
                    $priceRules
                );

                if ($duplicatePriceRuleKey) {
                    $rowsToHighlight = array_merge($duplicatePriceRuleKey, $rowsToHighlight);
                    $objProduct = new Product((int) $idRoomType, false, $this->context->language->id);
                    $this->errors[] = sprintf($this->l('An advanced price rule already exists for "%s". Please update the restrictions to avoid duplication.'), $objProduct->name);
                }
            }
        }

        $rowsToHighlight = array_values($rowsToHighlight);
        $this->context->smarty->assign('rowsToHighlight', $rowsToHighlight);
        if (!($this->errors)) {
            if ($idFeaturePrice) {
                $objFeaturePricing = new HotelRoomTypeFeaturePricing($idFeaturePrice);
            }

            // lang fields
            $objFeaturePricing->impact_way = $priceImpactWay;
            $objFeaturePricing->impact_type = $priceImpactType;
            $objFeaturePricing->impact_value = $impactValue;
            $objFeaturePricing->active = $enableFeaturePrice;

            // set the values of the groups for this feature price
            $objFeaturePricing->groupBox = $groups;
            $objFeaturePricing->id_product = $roomTypeId;
            if ($createMultiple) {
                foreach ($roomTypeIds as $idRoomType) {
                    $objFeaturePricing->id_product = $idRoomType;
                    $objFeaturePricing->feature_price_name = $featurePricingName[$idRoomType];
                    $objFeaturePricing->add();
                    $objFeaturePricing->saveFeaturePriceRestrictions($objFeaturePricing->id, $priceRules);
                }

                Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
            } else {
                $objFeaturePricing->feature_price_name = $featurePricingName[$roomTypeId];
            }

            $conf = $idFeaturePrice ? 4 : 3;
            if ($objFeaturePricing->save()) {
                $objFeaturePricing->saveFeaturePriceRestrictions($objFeaturePricing->id, $priceRules);
                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    Tools::redirectAdmin(
                        self::$currentIndex.'&id_feature_price='.(int) $objFeaturePricing->id.
                        '&update'.$this->table.'&conf='.$conf.'&token='.$this->token
                    );
                } else {
                    Tools::redirectAdmin(self::$currentIndex.'&conf='.$conf.'&token='.$this->token);
                }
            } else {
                $this->errors[] = $this->l('Some error occured while saving advanced price rule.');
            }
        }
        if (isset($idFeaturePrice) && $idFeaturePrice) {
            $this->display = 'edit';
        } else {
            $this->display = 'add';
        }
    }

    public function ajaxProcessSearchProductByName()
    {
        $response = array('status' => 'failed');
        if ($productName = Tools::getValue('room_type_name')) {
            if ($productsByName = Product::searchByName($this->context->language->id, $productName)) {
                $productsByName = HotelBranchInformation::filterDataByHotelAccess(
                    $productsByName,
                    $this->context->employee->id_profile
                );
                // filter room types as per accessed hotels
                foreach ($productsByName as &$product) {
                    $hotelRoomType = new HotelRoomType();
                    $roomInfoByIdProduct = $hotelRoomType->getRoomTypeInfoByIdProduct($product['id_product']);
                    $idHotel = $roomInfoByIdProduct['id_hotel'];
                    if (isset($idHotel) && $idHotel) {
                        $onjBranchInfo = new HotelBranchInformation($idHotel, $this->context->language->id);
                        $product['name'].= ' / '.$onjBranchInfo->hotel_name;
                    }
                }

                $response = $productsByName;
            } else {
                $response['msg'] = $this->l('No match found for entered room type name.');
            }
        } else {
            $response['msg'] = $this->l('No match found for entered room type name.');
        }

        $this->ajaxDie(json_encode($response));
    }

    public function setMedia()
    {
        parent::setMedia();

        Media::addJsDef(array(
            'date_selection_types' => array(
                'range' => array(
                    'value' => HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE,
                    'title' => $this->l('Date Range', null, true)
                ),
                'specific' => array(
                    'value' => HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC,
                    'title' => $this->l('Specific Date', null, true)
                ),
            ),
            'impact_ways' => array(
                'decrease' => HotelRoomTypeFeaturePricing::IMPACT_WAY_DECREASE,
                'increase' => HotelRoomTypeFeaturePricing::IMPACT_WAY_INCREASE,
                'fix' => HotelRoomTypeFeaturePricing::IMPACT_WAY_FIX_PRICE,
            ),
            'impact_types' => array(
                'percentage' => HotelRoomTypeFeaturePricing::IMPACT_TYPE_PERCENTAGE,
                'fixed' => HotelRoomTypeFeaturePricing::IMPACT_TYPE_FIXED_PRICE,
            ),
            'week_days' => $this->getWeekDays(),
            'dateSelectionTitle' => $this->l('Date Selection type', null, true),
            'specificDateText' => $this->l('Specific Date', null, true),
            'dateFromText' => $this->l('Date From', null, true),
            'dateToText' => $this->l('Date to', null, true),
            'yesText' => $this->l('Yes', null, true),
            'noText' => $this->l('No', null, true),
            'specialDaysText' => $this->l('Restrict to Week Days', null, true),
            'weekDaysText' => $this->l('Select Week Days', null, true),
            'specialDaysTooltipText' => $this->l('Enable this option to add restriction to specific week days (for example, weekends) of the selected date range.', null, true)
        ));

        $this->addCSS(_MODULE_DIR_.'hotelreservationsystem/views/css/HotelReservationAdmin.css');
        $this->addJs(_MODULE_DIR_.'hotelreservationsystem/views/js/HotelReservationAdmin.js');
    }
}
