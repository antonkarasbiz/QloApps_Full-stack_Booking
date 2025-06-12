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

class HotelBedType extends ObjectModel
{
    public $length;
    public $width;
    public $name;

    public static $definition =array(
        'table' => 'htl_bed_type',
        'primary' => 'id_bed_type',
        'multilang' => true,
        'fields' => array(
            'length' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'width' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            // lang fields
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
        ),
    );

    public function getAllBedTypes($idLang)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $sql = 'SELECT hbt.*, hbtl.`name` FROM `'._DB_PREFIX_.$this->table.'` hbt
            LEFT JOIN `'._DB_PREFIX_.$this->table.'_lang` hbtl ON hbt.`id_bed_type` = hbtl.`id_bed_type`
            WHERE 1 AND hbtl.`id_lang` ='.(int) $idLang;

        return Db::getInstance()->executeS($sql);
    }

    public function delete()
    {
        $objHotelRoomTypeBedType = new HotelRoomTypeBedType();
        $objHotelRoomTypeBedType->deleteRoomTypeBedTypes($this->id);

        return parent::delete();
    }
}
