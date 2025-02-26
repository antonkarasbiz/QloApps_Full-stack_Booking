<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*/


class ServiceProductOption extends ObjectModel
{
    public $id_service_product_option;
    public $id_product;
    public $name;
    public $price_impact;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'service_product_option',
        'primary' => 'id_service_product_option',
        'multilang' => true,
        'fields' => array(
            'id_product' =>     array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'price_impact' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'lang' => true, 'required' => true),
        )
    );

    public function addProductOption($idProduct, $names, $priceImpact)
    {
        if (!is_array($names)) {
            return false;
        }

        $objServiceProductOption = new ServiceProductOption();
        $objServiceProductOption->id_product = $idProduct;
        $objServiceProductOption->price_impact = $priceImpact;
        foreach($names as $id_lang => $name) {
            $objServiceProductOption->name[$id_lang] = $name;
        }
        $objServiceProductOption->save();
    }

    public static function productHasOptions($idProduct, $idServiceProductOption = false)
    {

        $sql = 'SELECT po.`id_service_product_option`
            FROM `'._DB_PREFIX_.'service_product_option` po
            WHERE po.`id_product` = '.(int)$idProduct;
        if ($idServiceProductOption) {
            $sql .= ' AND po.`id_service_product_option` = '.(int)$idServiceProductOption;
        }
        return (bool)Db::getInstance()->getValue($sql);
    }

    public function  getProductOptions($idProduct, $idServiceProductOption = false, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $sql = 'SELECT po.`id_service_product_option`, po.`id_product`, pol.`id_lang`, pol.`name`, po.`price_impact`
            FROM `'._DB_PREFIX_.'service_product_option` po
            INNER JOIN `'._DB_PREFIX_.'service_product_option_lang` pol
            ON (po.`id_service_product_option` = pol.`id_service_product_option`)
            WHERE po.`id_product` = '.(int)$idProduct.' AND pol.`id_lang` = '.(int)$idLang;

        if ($idServiceProductOption) {
            $sql .= ' AND po.`id_service_product_option` = '.(int)$idServiceProductOption;
            return Db::getInstance()->getRow($sql);
        } else {
            return Db::getInstance()->executeS($sql);
        }
    }
}