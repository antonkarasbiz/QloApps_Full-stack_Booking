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

class HotelRoomTypeBedType extends ObjectModel
{
    public $length;
    public $width;
    public $name;

    public static $definition =array(
        'table' => 'htl_room_type_bed_type',
        'primary' => 'id_bed_type',
        'multilang' => true,
        'fields' => array(
            'length' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'width' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),

            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
        ),
    );

    public function getAllBedTypes($idLang)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $sql = 'SELECT hrtbt.*, hrtbtl.`name` FROM `'._DB_PREFIX_.$this->table.'` hrtbt
            LEFT JOIN `'._DB_PREFIX_.$this->table.'_lang` hrtbtl ON hrtbt.`id_bed_type` = hrtbtl.`id_bed_type`
            WHERE 1 AND hrtbtl.`id_lang` ='.(int) $idLang;

        return Db::getInstance()->executeS($sql);
    }

}
