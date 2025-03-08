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


class RoomTypeServiceProductOrderDetail extends ObjectModel
{
    public $id_product;
    public $id_order;
    public $id_order_detail;
    public $id_cart;
    public $id_hotel;
    public $id_htl_booking_detail;
    public $id_product_option;
    public $unit_price_tax_excl;
    public $unit_price_tax_incl;
    public $total_price_tax_excl;
    public $total_price_tax_incl;
    public $name;
    public $option_name;
    public $quantity;
    public $auto_added;
    public $is_refunded;
    public $is_cancelled;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'htl_room_type_service_product_order_detail',
        'primary' => 'id_room_type_service_product_order_detail',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order_detail' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_htl_booking_detail' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'unit_price_tax_excl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'unit_price_tax_incl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_price_tax_excl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_price_tax_incl' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'required' => true),
            'option_name' => array('type' => self::TYPE_STRING),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'auto_added' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'is_refunded' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'is_cancelled' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function getroomTypeServiceProducts(
        $idOrder,
        $idProduct = 0,
        $idHotel = 0,
        $roomTypeIdProduct = 0,
        $dateFrom = 0,
        $dateTo = 0,
        $idRoom = 0,
        $getTotalPrice = 0,
        $useTax = null,
        $autoAddToCart = 0,
        $priceAdditionType = null,
        $idOrderDetail = 0,
        $idHtlBookingDetail = 0
    ) {
        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }

        $sql = 'SELECT rsod.*';
        if (!$getTotalPrice) {
            $sql .= ', hbd.`id_product` as `id_room_type`, od.`product_allow_multiple_quantity`, od.`product_price_calculation_method`, hbd.`id_room`, hbd.`adults`, hbd.`children`';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_booking_detail` hbd
            LEFT JOIN `'._DB_PREFIX_.'htl_room_type_service_product_order_detail` rsod ON(rsod.`id_htl_booking_detail` = hbd.`id`)';

        $sql .= ' LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON(od.`id_order_detail` = rsod.`id_order_detail`)';
        $sql .= ' WHERE rsod.`id_order` = '.(int)$idOrder;

        if ($idOrderDetail) {
            $sql .= ' AND rsod.`id_order_detail` = '.(int)$idOrderDetail;
        }

        if (!is_null($autoAddToCart)) {
            $sql .= ' AND od.`product_auto_add` = '. (int)$autoAddToCart;
            if ($autoAddToCart == 1 && !is_null($priceAdditionType)) {
                $sql .= ' AND od.`product_price_addition_type` = '.$priceAdditionType;
            }
        }
        if ($idProduct) {
            $sql .= ' AND rsod.`id_product`='.(int) $idProduct;
        }
        if ($idHotel) {
            $sql .= ' AND hbd.`id_hotel`='.(int) $idHotel;
        }
        if ($roomTypeIdProduct) {
            $sql .= ' AND hbd.`id_product`='.(int) $roomTypeIdProduct;
        }
        if ($dateFrom && $dateTo) {
            $sql .= ' AND hbd.`date_from` = \''.pSQL($dateFrom).'\' AND hbd.`date_to` = \''.pSQL($dateTo).'\'';
        }
        if ($idRoom) {
            $sql .= ' AND hbd.`id_room`='.(int) $idRoom;
        }
        if ($idHtlBookingDetail) {
            $sql .= ' AND hbd.`id` = '.(int)$idHtlBookingDetail;
        }
        $sql .= ' ORDER BY hbd.`id`';

        if ($getTotalPrice) {
            $totalPrice = 0;
        }

        $selectedAdditionalServices = array();
        if ($additionalServices = Db::getInstance()->executeS($sql)) {
            $moduleObj = Module::getInstanceByName('hotelreservationsystem');
            foreach ($additionalServices as $product) {
                if ($getTotalPrice) {
                    if ($useTax) {
                        $totalPrice += $product['total_price_tax_incl'];
                    } else {
                        $totalPrice += $product['total_price_tax_excl'];
                    }
                } else {

                    $taxes = OrderDetailCore::getTaxListStatic($product['id_order_detail']);
                    $tax_temp = array();
                    foreach ($taxes as $tax) {
                        $obj = new Tax($tax['id_tax']);
                        $tax_temp[] = sprintf($moduleObj->l('%1$s%2$s%%'), ($obj->rate + 0), '&nbsp;');
                    }
                    $product_tax = $taxes;
                    $product_tax_label = implode(', ', $tax_temp);
                    if (isset($selectedAdditionalServices[$product['id_htl_booking_detail']])) {
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['total_price_tax_excl'] += $product['total_price_tax_excl'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['total_price_tax_incl'] += $product['total_price_tax_incl'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['additional_services'][] = array(
                            'id_room_type_service_product_order_detail' => $product['id_room_type_service_product_order_detail'],
                            'id_order_detail' => $product['id_order_detail'],
                            'id_product' => $product['id_product'],
                            'name' => $product['name'],
                            'quantity' => $product['quantity'],
                            'product_tax' => $product_tax,
                            'product_tax_label' => $product_tax_label,
                            'allow_multiple_quantity' => $product['product_allow_multiple_quantity'],
                            'price_calculation_method' => $product['product_price_calculation_method'],
                            'total_price_tax_excl' => $product['total_price_tax_excl'],
                            'total_price_tax_incl' => $product['total_price_tax_incl'],
                        );
                    } else {
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['id_order'] = $product['id_order'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['id_cart'] = $product['id_cart'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['id_htl_booking_detail'] = $product['id_htl_booking_detail'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['adults'] = $product['adults'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['children'] = $product['children'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['total_price_tax_excl'] = $product['total_price_tax_excl'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['total_price_tax_incl'] = $product['total_price_tax_incl'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['id_room_type'] = $product['id_room_type'];
                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['id_room'] = $product['id_room'];


                        $selectedAdditionalServices[$product['id_htl_booking_detail']]['additional_services'] = array(
                            array(
                                'id_room_type_service_product_order_detail' => $product['id_room_type_service_product_order_detail'],
                                'id_order_detail' => $product['id_order_detail'],
                                'id_product' => $product['id_product'],
                                'name' => $product['name'],
                                'quantity' => $product['quantity'],
                                'allow_multiple_quantity' => $product['product_allow_multiple_quantity'],
                                'price_calculation_method' => $product['product_price_calculation_method'],
                                'product_tax' => $product_tax,
                                'product_tax_label' => $product_tax_label,
                                'total_price_tax_excl' => $product['total_price_tax_excl'],
                                'total_price_tax_incl' => $product['total_price_tax_incl'],
                            ),
                        );
                    }
                }
            }
        }

        if ($getTotalPrice) {
            return $totalPrice;
        }
        return $selectedAdditionalServices;
    }

    public function getSelectedServicesForRoom(
        $idHotelBookingDetail,
        $getTotalPrice = 0,
        $useTax = null,
        $autoAddToCart = null,
        $priceAdditionType = null
    ) {

        if ($useTax === null) {
            $useTax = Product::$_taxCalculationMethod == PS_TAX_EXC ? false : true;
        }

        $sql = 'SELECT rsod.*';
        if (!$getTotalPrice) {
            $sql .= ', hbd.`id_product` as `id_room_type`, hbd.`id_room`, od.`product_allow_multiple_quantity`, p.`max_quantity`,
                od.`product_auto_add`, od.`product_price_calculation_method`, od.`product_price_addition_type`';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_booking_detail` hbd
            INNER JOIN `'._DB_PREFIX_.'htl_room_type_service_product_order_detail` rsod ON(rsod.`id_htl_booking_detail` = hbd.`id`)';

        $sql .= ' LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON(od.`id_order_detail` = rsod.`id_order_detail`)';
        $sql .= ' LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = rsod.`id_product`)';

        $sql .= ' WHERE hbd.`id` = '.(int)$idHotelBookingDetail;

        if (!is_null($autoAddToCart)) {
            $sql .= ' AND od.`product_auto_add` = '. (int)$autoAddToCart;
            if ($autoAddToCart == 1 && !is_null($priceAdditionType)) {
                $sql .= ' AND od.`product_price_addition_type` = '.$priceAdditionType;
            }
        }

        if ($getTotalPrice) {
            $totalPrice = 0;
        }
        $selectedAdditionalServices = array();
        if ($additionalServices = Db::getInstance()->executeS($sql)) {
            foreach ($additionalServices as $product) {
                if ($getTotalPrice) {
                    if ($useTax) {
                        $totalPrice += $product['total_price_tax_incl'];
                    } else {
                        $totalPrice += $product['total_price_tax_excl'];
                    }
                } else {
                    if (isset($selectedAdditionalServices['additional_services'])) {
                        $selectedAdditionalServices['total_price_tax_excl'] += $product['total_price_tax_excl'];
                        $selectedAdditionalServices['total_price_tax_incl'] += $product['total_price_tax_incl'];
                        $selectedAdditionalServices['additional_services'][] = array(
                            'id_room_type_service_product_order_detail' => $product['id_room_type_service_product_order_detail'],
                            'id_order_detail' => $product['id_order_detail'],
                            'id_product' => $product['id_product'],
                            'name' => $product['name'],
                            'quantity' => $product['quantity'],
                            'allow_multiple_quantity' => $product['product_allow_multiple_quantity'],
                            'max_quantity' => $product['max_quantity'],
                            'product_auto_add' => $product['product_auto_add'],
                            'product_price_addition_type' => $product['product_price_addition_type'],
                            'product_price_calculation_method' => $product['product_price_calculation_method'],
                            'unit_price_tax_excl' => $product['unit_price_tax_excl'],
                            'unit_price_tax_incl' => $product['unit_price_tax_incl'],
                            'total_price_tax_excl' => $product['total_price_tax_excl'],
                            'total_price_tax_incl' => $product['total_price_tax_incl'],
                        );
                    } else {
                        $selectedAdditionalServices['id_order'] = $product['id_order'];
                        $selectedAdditionalServices['id_cart'] = $product['id_cart'];
                        $selectedAdditionalServices['id_htl_booking_detail'] = $product['id_htl_booking_detail'];
                        $selectedAdditionalServices['total_price_tax_excl'] = $product['total_price_tax_excl'];
                        $selectedAdditionalServices['total_price_tax_incl'] = $product['total_price_tax_incl'];
                        $selectedAdditionalServices['id_room_type'] = $product['id_room_type'];
                        $selectedAdditionalServices['id_room'] = $product['id_room'];
                        $selectedAdditionalServices['additional_services'] = array(
                            array(
                                'id_room_type_service_product_order_detail' => $product['id_room_type_service_product_order_detail'],
                                'id_order_detail' => $product['id_order_detail'],
                                'id_product' => $product['id_product'],
                                'name' => $product['name'],
                                'quantity' => $product['quantity'],
                                'allow_multiple_quantity' => $product['product_allow_multiple_quantity'],
                                'max_quantity' => $product['max_quantity'],
                                'product_auto_add' => $product['product_auto_add'],
                                'product_price_addition_type' => $product['product_price_addition_type'],
                                'product_price_calculation_method' => $product['product_price_calculation_method'],
                                'unit_price_tax_excl' => $product['unit_price_tax_excl'],
                                'unit_price_tax_incl' => $product['unit_price_tax_incl'],
                                'total_price_tax_excl' => $product['total_price_tax_excl'],
                                'total_price_tax_incl' => $product['total_price_tax_incl'],
                            ),
                        );
                    }
                }
            }
        }
        if ($getTotalPrice) {
            return $totalPrice;
        }
        return $selectedAdditionalServices;
    }

    public function deleteRoomSevices($idHotelBookingDetail)
    {
        $services = Db::getInstance()->executeS(
            'SELECT `id_room_type_service_product_order_detail` FROM `'._DB_PREFIX_.'htl_room_type_service_product_order_detail` pod
            WHERE `id_htl_booking_detail` = '.(int)$idHotelBookingDetail
        );
        $res = true;
        foreach ($services as $service) {
            $objServiceProductOrderDetail = new self($service['id_room_type_service_product_order_detail']);
            $res &= $objServiceProductOrderDetail->delete();
        }

        return $res;
    }

    public function getProducts($idOrder, $idOrderDetail = 0, $idProduct = 0, $serviceProductType = 0)
    {
        $sql = 'SELECT spo.* FROM `'._DB_PREFIX_.'htl_room_type_service_product_order_detail` spo';

        if ($serviceProductType) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = spo.`id_order_detail` AND od.`id_order` = '.(int)$idOrder.')';
        }

        $sql .= ' WHERE 1 AND spo.`id_order` = '.(int)$idOrder;

        if ($idOrderDetail) {
            $sql .= ' AND spo.`id_order_detail` = '.(int)$idOrderDetail;
        }

        if ($idProduct) {
            $sql .= ' AND spo.`id_product` = '.(int)$idProduct;
        }

        if ($serviceProductType) {
            $sql .= ' AND od.`selling_preference_type` = '.(int)$serviceProductType;
        }

        $products = Db::getInstance()->executeS($sql);
        foreach ($products as $key => $product) {
            // Check if this booking as any refund history then enter refund data
            if ($refundInfo = OrderReturn::getOrdersReturnDetail($idOrder, 0, 0, $product['id_room_type_service_product_order_detail'])) {
                $products[$key]['refund_info'] = reset($refundInfo);
            }
        }

        return $products;
    }

    public function processRefundInTable()
    {
        if (Validate::isLoadedObject($this)) {
            $reduction_amount = array(
                'total_price_tax_excl' => 0,
                'total_price_tax_incl' => 0,
                'total_products_tax_excl' => 0,
                'total_products_tax_incl' => 0,
            );
            $objOrder = new Order($this->id_order);
            $orderTotalPaid = $objOrder->getTotalPaid();
            $orderDiscounts = $objOrder->getCartRules();

            $hasOrderDiscountOrPayment = ((float)$orderTotalPaid > 0 || $orderDiscounts) ? true : false;

            // things to do if order is not paid
            if (!$hasOrderDiscountOrPayment) {
                $objHotelBookingDemands = new HotelBookingDemands();
                $objRoomTypeServiceProductOrderDetail = new RoomTypeServiceProductOrderDetail();

                $reduction_amount['total_price_tax_excl'] = (float) $this->total_price_tax_excl;
                $reduction_amount['total_products_tax_excl'] = (float) $this->total_price_tax_excl;
                $reduction_amount['total_price_tax_incl'] = (float) $this->total_price_tax_incl;
                $reduction_amount['total_products_tax_incl'] = (float) $this->total_price_tax_incl;
            }

            // enter refunded quantity in the order detail table
            $idOrderDetail = $this->id_order_detail;
            if (Validate::isLoadedObject($objOrderDetail = new OrderDetail($idOrderDetail))) {

                $objOrderDetail->product_quantity_refunded += $this->quantity;
                if ($objOrderDetail->product_quantity_refunded > $objOrderDetail->product_quantity) {
                    $objOrderDetail->product_quantity_refunded = $objOrderDetail->product_quantity;
                }

                if (!$hasOrderDiscountOrPayment) {
                    // reduce room amount from order and order detail
                    $objOrderDetail->total_price_tax_incl -= Tools::processPriceRounding(
                        $this->total_price_tax_incl,
                        1,
                        $objOrder->round_type,
                        $objOrder->round_mode
                    );

                    $objOrderDetail->total_price_tax_excl -= Tools::processPriceRounding(
                        $this->total_price_tax_excl,
                        1,
                        $objOrder->round_type,
                        $objOrder->round_mode
                    );

                    if (Validate::isLoadedObject($objOrder = new Order($this->id_order))) {
                        $objOrder->total_paid = Tools::ps_round(
                            ($objOrder->total_paid - $reduction_amount['total_price_tax_incl']),
                            _PS_PRICE_COMPUTE_PRECISION_
                        );
                        $objOrder->total_paid = $objOrder->total_paid > 0 ? $objOrder->total_paid : 0;

                        $objOrder->total_paid_tax_excl = Tools::ps_round(($objOrder->total_paid_tax_excl - $reduction_amount['total_price_tax_excl']),
                            _PS_PRICE_COMPUTE_PRECISION_
                        );
                        $objOrder->total_paid_tax_excl = $objOrder->total_paid_tax_excl > 0 ? $objOrder->total_paid_tax_excl : 0;

                        $objOrder->total_paid_tax_incl = Tools::ps_round(($objOrder->total_paid_tax_incl - $reduction_amount['total_price_tax_incl']),
                            _PS_PRICE_COMPUTE_PRECISION_
                        );
                        $objOrder->total_paid_tax_incl = $objOrder->total_paid_tax_incl > 0 ? $objOrder->total_paid_tax_incl : 0;

                        $objOrder->total_products = Tools::ps_round(($objOrder->total_products - $reduction_amount['total_products_tax_excl']),
                            _PS_PRICE_COMPUTE_PRECISION_
                        );
                        $objOrder->total_products = $objOrder->total_products > 0 ? $objOrder->total_products : 0;

                        $objOrder->total_products_wt = Tools::ps_round(($objOrder->total_products_wt - $reduction_amount['total_products_tax_incl']),
                            _PS_PRICE_COMPUTE_PRECISION_
                        );
                        $objOrder->total_products_wt = $objOrder->total_products_wt > 0 ? $objOrder->total_products_wt : 0;

                        $objOrder->save();
                    }
                }

                $objOrderDetail->save();
            }

            // as refund is completed then set the booking as refunded
            $this->is_refunded = 1;
            if (!$hasOrderDiscountOrPayment) {
                // Reduce room amount from htl_booking_detail
                $this->is_cancelled = 1;
                $this->total_price_tax_excl = 0;
                $this->total_price_tax_incl = 0;
            }

            $this->save();

            return true;
        }

        return false;
    }

    public function delete()
    {
        // delete entry from order detail table.
        return parent::delete();
    }
}