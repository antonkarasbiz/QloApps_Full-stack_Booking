<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/license/osl-3.0-php
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
 * @license https://opensource.org/license/osl-3.0-php Open Software License version 3.0
 */

class HTMLTemplateBookingVoucherCore extends HTMLTemplate
{
    /** @var Order */
    public $order;
    public $available_in_your_account = false;

    /**
     * @param Order $order
     * @param Smarty $smarty
     */
    public function __construct(Order $order, $smarty)
    {
        $this->order = $order;
        $this->smarty = $smarty;
        $this->date = Tools::displayDate(date('Y-m-d'));
        $this->title = sprintf(self::l('Booking Voucher - %s'), $this->order->reference);
        $this->shop = new Shop((int)$this->order->id_shop);
    }

    /**
     * @return string HTML header
     */
    public function getHeader()
    {
        $this->assignCommonHeaderData();
        $this->smarty->assign(array('header' => self::l('Booking Voucher')));

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    /**
     * Assembles all voucher data inline and renders the template.
     *
     * @return string HTML content
     */
    public function getContent()
    {
        // --- Bookings ---
        $objHotelBookingDetail = new HotelBookingDetail();
        $bookings = $objHotelBookingDetail->getBookingDataByOrderId((int)$this->order->id);
        if (!is_array($bookings)) {
            $bookings = array();
        }

        // --- Order state ---
        $orderState = $this->order->getCurrentStateFull((int)$this->order->id_lang);
        if (!is_array($orderState)) {
            $orderState = array('name' => self::l('Confirmed'));
        } elseif (empty($orderState['name'])) {
            $orderState['name'] = self::l('Confirmed');
        }

        // --- Hotel / property ---
        $hotelName = Configuration::get('PS_SHOP_NAME', null, null, (int)$this->order->id_shop);
        $hotelPhone = Configuration::get('PS_SHOP_PHONE', null, null, (int)$this->order->id_shop);
        $hotelEmail = Configuration::get('PS_SHOP_EMAIL', null, null, (int)$this->order->id_shop);
        $hotelAddress = '';
        $checkInTime = '--';
        $checkOutTime = '--';
        $paymentPolicy = '';
        $cancellationPolicy = '';

        if (!empty($bookings)) {
            $firstBooking = reset($bookings);
            if (!empty($firstBooking['id_hotel'])) {
                $objHotel = new HotelBranchInformation((int)$firstBooking['id_hotel'], (int)$this->order->id_lang);
                if (Validate::isLoadedObject($objHotel)) {
                    $idHotel = (int)$objHotel->id;
                    $hotelName = $objHotel->hotel_name ? $objHotel->hotel_name : $hotelName;
                    $hotelPhone = $objHotel->phone ? $objHotel->phone : $hotelPhone;
                    $hotelEmail = $objHotel->email ? $objHotel->email : $hotelEmail;
                    $checkInTime = $objHotel->check_in ?: '--';
                    $checkOutTime = $objHotel->check_out ?: '--';
                    $policies = is_array($objHotel->policies) ? '' : (string)$objHotel->policies;
                    if ($policies) {
                        $policyText = html_entity_decode($policies, ENT_QUOTES, 'UTF-8');
                        $policyText = preg_replace('/<br\\s*\\/?>/i', "\n", $policyText);
                        $policyText = strip_tags($policyText);
                        $policyText = preg_replace("/[\r\n]+/", "\n", $policyText);
                        $paymentPolicy = nl2br(trim($policyText));
                    }
                    $cancellationPolicy = $this->getFormattedCancellationPolicy($idHotel);

                    if ($idHotelAddress = $objHotel->getHotelIdAddress()) {
                        $objHotelAddress = new Address((int)$idHotelAddress);
                        if (Validate::isLoadedObject($objHotelAddress)) {
                            $objHotelAddress->firstname = $hotelName;
                            $hotelAddress = AddressFormat::generateAddress(
                                $objHotelAddress,
                                array('avoid' => array('lastname')),
                                '<br />',
                                ' '
                            );
                        }
                    }
                }
            }
        }

        if (!$hotelAddress) {
            $shopAddress = $this->shop->getAddress();
            if ($shopAddress instanceof Address) {
                $hotelAddress = AddressFormat::generateAddress($shopAddress, array(), '<br />', ' ');
            }
        }

        // --- Room and service data ---
        $roomAndServiceData = $this->getRoomAndServiceData($bookings);

        // --- Guest details ---
        $guestName = '';
        $guestEmail = '';
        $guestPhone = '';
        $guestAddress = '';

        $customer = new Customer((int)$this->order->id_customer);
        if (Validate::isLoadedObject($customer)) {
            $guestName = trim($customer->firstname.' '.$customer->lastname);
            $guestEmail = $customer->email;
            $guestPhone = $customer->phone;
        }
        $addressId = (int)$this->order->id_address_invoice ?: (int)$this->order->id_address_delivery;
        if ($addressId) {
            $address = new Address($addressId);
            if (Validate::isLoadedObject($address)) {
                $guestPhone = $address->phone_mobile ? $address->phone_mobile : $address->phone;
                $guestAddress = AddressFormat::generateAddress($address, array(), '<br />', ' ');
            }
        }
        // Override with primary guest if this is a guest booking
        if ($idCustomerGuestDetail = OrderCustomerGuestDetail::isCustomerGuestBooking((int)$this->order->id)) {
            $traveler = new OrderCustomerGuestDetail((int)$idCustomerGuestDetail);
            if (Validate::isLoadedObject($traveler)) {
                $guestName = trim($traveler->firstname.' '.$traveler->lastname);
                $guestEmail = $traveler->email;
                $guestPhone = $traveler->phone;
            }
        }

        $footer = $this->getTotalsData($roomAndServiceData);

        // --- Payment ---
        $paymentMethods = array();
        $payments = $this->order->getOrderPaymentDetail();
        if (is_array($payments) && $payments) {
            foreach ($payments as $paymentDetail) {
                $method = isset($paymentDetail['payment_method']) ? trim($paymentDetail['payment_method']) : '';
                if (!$method) {
                    $method = $this->order->payment;
                }
                $amount = isset($paymentDetail['real_paid_amount'])
                    ? (float)$paymentDetail['real_paid_amount']
                    : (float)$paymentDetail['amount'];
                $idCurrency = isset($paymentDetail['id_currency'])
                    ? (int)$paymentDetail['id_currency']
                    : (int)$this->order->id_currency;
                $paymentMethods[] = $method.' - '.Tools::displayPrice($amount, $idCurrency);
            }
        }
        $paymentMethod = $paymentMethods ? implode('<br /><br /> ', $paymentMethods) : $this->order->payment;

        $data = array(
            'order' => $this->order,
            'order_status' => $orderState['name'],
            'booking_date' => Tools::displayDate($this->order->date_add, null, false),
            'issue_date' => Tools::displayDate(date('Y-m-d'), null, false),
            // hotel / property
            'hotel_name' => $hotelName,
            'hotel_phone' => $hotelPhone,
            'hotel_email' => $hotelEmail,
            'hotel_address' => $hotelAddress,
            // guest
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'guest_address' => $guestAddress,
            'total_guests' => (int)$roomAndServiceData['total_guests'],
            // booking summary
            'total_rooms' => (int)$roomAndServiceData['total_rooms'],
            'total_room_nights' => (int)$roomAndServiceData['total_room_nights'],
            // room and service tables
            'room_type_rows' => $roomAndServiceData['room_type_rows'],
            'service_rows' => $roomAndServiceData['service_rows'],
            // payment
            'payment_method' => $paymentMethod,
            'payment_status' => $orderState['name'],
            'is_advance_payment' => (int)$this->order->is_advance_payment,
            // totals
            'footer' => $footer,
            // policies
            'check_in_time' => ($checkInTime && $checkInTime !== '--')
                ? date('h:i a', strtotime($checkInTime)) : '--',
            'check_out_time' => ($checkOutTime && $checkOutTime !== '--')
                ? date('h:i a', strtotime($checkOutTime)) : '--',
            'payment_policy' => $paymentPolicy
                ? $paymentPolicy
                : self::l('Please contact the property for policy details.'),
            'cancellation_policy' => $cancellationPolicy,
            'has_cancellation_policy' => (bool)trim(strip_tags($cancellationPolicy)),
            // tax
            'tax_breakdown' => $this->getTaxBreakdownData(),
        );

        if (Tools::getValue('debug')) {
            die(json_encode($data));
        }
        $this->smarty->assign($data);

        return $this->smarty->fetch($this->getTemplate('booking-voucher'));
    }

    /**
     * Builds per-room and per-room-type rows, services, price breakdown, and occupancy totals.
     *
     * @param array $bookings
     * @return array
     */
    protected function getRoomAndServiceData($bookings)
    {
        $rooms = array();
        $serviceRows = array();
        $servicesByRoom = array();
        $priceBreakdown = array();
        $roomTypeRows = array();
        $totalRoomNights = 0;
        $totalGuests = 0;

        foreach ($bookings as $booking) {
            $nights = (int)HotelHelper::getNumberOfDays($booking['date_from'], $booking['date_to']);
            if ($nights <= 0) {
                $nights = 1;
            }

            $roomReference = !empty($booking['room_num']) ? $booking['room_num'] : 'BK-'.(int)$booking['id'];
            $adults = (int)$booking['adults'];
            $children = (int)$booking['children'];
            $totalGuests += $adults + $children;
            $totalRoomNights += $nights;

            $serviceData = $this->getRoomServiceData($booking, $roomReference);
            $servicesByRoom[$roomReference] = $serviceData['services'];
            $serviceRows = array_merge($serviceRows, $serviceData['rows']);

            $roomTotalTaxExcl = (float)$booking['total_price_tax_excl'];
            $roomTotalWithAutoServicesTaxExcl = $roomTotalTaxExcl + $serviceData['auto_added_room_total_tax_excl'];
            $unitPriceTaxExcl = $nights ? Tools::ps_round(
                $roomTotalWithAutoServicesTaxExcl / $nights,
                _PS_PRICE_COMPUTE_PRECISION_,
                $this->order->round_mode
            ) : 0;

            // Group by room type + date range to aggregate multi-room rows
            $roomTypeRowKey = implode('|', array(
                (int)$booking['id_order_detail'],
                isset($booking['room_type_name']) ? $booking['room_type_name'] : '',
                isset($booking['date_from']) ? $booking['date_from'] : '',
                isset($booking['date_to']) ? $booking['date_to'] : '',
            ));

            if (!isset($roomTypeRows[$roomTypeRowKey])) {
                $idOrderDetail = (int)$booking['id_order_detail'];
                $detailTaxes = $idOrderDetail ? OrderDetail::getTaxListStatic($idOrderDetail) : null;
                $orderDetailTaxLabel = self::l('No tax');
                if ($detailTaxes) {
                    $taxTemp = array();
                    foreach ($detailTaxes as $tax) {
                        $objTax = new Tax($tax['id_tax']);
                        $taxTemp[] = sprintf(self::l('%1$s%2$s%%'), $objTax->rate + 0, '&nbsp;');
                    }
                    $orderDetailTaxLabel = implode(', ', $taxTemp);
                }

                $roomTypeRows[$roomTypeRowKey] = array(
                    'room_type_name' => isset($booking['room_type_name']) ? $booking['room_type_name'] : '',
                    'order_detail_tax_label' => $orderDetailTaxLabel,
                    'rooms' => 0,
                    'adults' => 0,
                    'children' => 0,
                    'nights' => $nights,
                    'check_in_date' => $this->formatDisplayDate($booking['date_from']),
                    'check_out_date' => $this->formatDisplayDate($booking['date_to']),
                    'unit_price_tax_excl' => 0,
                    'total_price_tax_excl' => 0,
                );
            }

            $roomTypeRows[$roomTypeRowKey]['rooms'] += 1;
            $roomTypeRows[$roomTypeRowKey]['adults'] += $adults;
            $roomTypeRows[$roomTypeRowKey]['children'] += $children;
            $roomTypeRows[$roomTypeRowKey]['total_price_tax_excl'] += $roomTotalWithAutoServicesTaxExcl;

            $rooms[] = array(
                'room_ref' => $roomReference,
                'room_num' => isset($booking['room_num']) ? $booking['room_num'] : '',
                'room_type_name' => isset($booking['room_type_name']) ? $booking['room_type_name'] : '',
                'check_in_date' => $this->formatDisplayDate($booking['date_from']),
                'check_out_date' => $this->formatDisplayDate($booking['date_to']),
                'nights' => $nights,
                'adults' => $adults,
                'children' => $children,
                'unit_price_tax_excl' => $unitPriceTaxExcl,
                'total_price_tax_excl' => $roomTotalWithAutoServicesTaxExcl,
            );

            $priceBreakdown[] = array(
                'room_ref' => $roomReference,
                'room_type_name' => isset($booking['room_type_name']) ? $booking['room_type_name'] : '',
                'nights' => $nights,
                'unit_price_tax_excl' => $unitPriceTaxExcl,
                'room_total_tax_excl' => $roomTotalWithAutoServicesTaxExcl,
                'services_total_tax_excl' => $serviceData['total_price_tax_excl'],
                'subtotal_tax_excl' => $roomTotalWithAutoServicesTaxExcl + $serviceData['total_price_tax_excl'],
            );
        }

        foreach ($roomTypeRows as &$roomTypeRow) {
            $denominator = (int)$roomTypeRow['nights'] * (int)$roomTypeRow['rooms'];
            if ($denominator > 0) {
                $roomTypeRow['unit_price_tax_excl'] = Tools::ps_round(
                    $roomTypeRow['total_price_tax_excl'] / $denominator,
                    6,
                    $this->order->round_mode
                );
            }
        }
        unset($roomTypeRow);

        return array(
            'rooms' => $rooms,
            'room_type_rows' => array_values($roomTypeRows),
            'service_rows' => $serviceRows,
            'services_by_room' => $servicesByRoom,
            'price_breakdown' => $priceBreakdown,
            'total_rooms' => count($rooms),
            'total_room_nights' => $totalRoomNights,
            'total_guests' => $totalGuests,
        );
    }

    protected function getRoomServiceData($booking, $roomReference)
    {
        $rows = array();
        $services = array();
        $totalPriceTaxExcl = 0;

        $objBookingDemand = new HotelBookingDemands();
        $extraDemands = $objBookingDemand->getRoomTypeBookingExtraDemands(
            (int)$this->order->id,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            (int)$booking['id'],
            (int)$booking['id_order_detail']
        );

        if (is_array($extraDemands)) {
            foreach ($extraDemands as $demand) {
                $services[] = array(
                    'room_ref' => $roomReference,
                    'check_in_out' => $this->formatDisplayDate($booking['date_from']).' - '.$this->formatDisplayDate($booking['date_to']),
                    'name' => $demand['name'],
                    'quantity' => 1,
                    'total_price_tax_excl' => (float)$demand['total_price_tax_excl'],
                );
                $rows[] = array(
                    'room_ref' => $roomReference,
                    'check_in_date' => $this->formatDisplayDate($booking['date_from']),
                    'check_out_date' => $this->formatDisplayDate($booking['date_to']),
                    'name' => $demand['name'],
                    'quantity' => 1,
                );
                $totalPriceTaxExcl += (float)$demand['total_price_tax_excl'];
            }
        }

        $objServiceProductOrderDetail = new ServiceProductOrderDetail();
        $autoAddedWithRoomPriceTaxExcl = (float)$objServiceProductOrderDetail->getRoomTypeServiceProducts(
            (int)$this->order->id,
            0,
            0,
            isset($booking['id_product']) ? (int)$booking['id_product'] : 0,
            isset($booking['date_from']) ? $booking['date_from'] : 0,
            isset($booking['date_to']) ? $booking['date_to'] : 0,
            isset($booking['id_room']) ? (int)$booking['id_room'] : 0,
            1,
            0,
            1,
            Product::PRICE_ADDITION_TYPE_WITH_ROOM
        );

        $additionalServices = $objServiceProductOrderDetail->getRoomTypeServiceProducts(
            0, 0, 0, 0, 0, 0, 0, 0, 1, 0, null, 0, (int)$booking['id']
        );

        if (isset($additionalServices[(int)$booking['id']]['additional_services'])) {
            foreach ($additionalServices[(int)$booking['id']]['additional_services'] as $service) {
                $services[] = array(
                    'room_ref' => $roomReference,
                    'check_in_out' => $this->formatDisplayDate($booking['date_from']).' - '.$this->formatDisplayDate($booking['date_to']),
                    'name' => $service['name'],
                    'quantity' => (int)$service['quantity'],
                    'total_price_tax_excl' => (float)$service['total_price_tax_excl'],
                    'product_tax_label' => isset($service['product_tax_label']) ? $service['product_tax_label'] : self::l('No tax'),
                );
                $rows[] = array(
                    'room_ref' => $roomReference,
                    'check_in_date' => $this->formatDisplayDate($booking['date_from']),
                    'check_out_date' => $this->formatDisplayDate($booking['date_to']),
                    'name' => $service['name'],
                    'quantity' => (int)$service['quantity'],
                    'total_price_tax_excl' => (float)$service['total_price_tax_excl'],
                    'product_tax_label' => isset($service['product_tax_label']) ? $service['product_tax_label'] : self::l('No tax'),
                );
                $totalPriceTaxExcl += (float)$service['total_price_tax_excl'];
            }
        }

        return array(
            'services' => $services,
            'rows' => $rows,
            'total_price_tax_excl' => $totalPriceTaxExcl,
            'auto_added_room_total_tax_excl' => $autoAddedWithRoomPriceTaxExcl,
        );
    }

    /**
     * Computes all cost totals using Order's existing methods.
     * Mirrors the $footer calculation in HTMLTemplateInvoice::getContent().
     *
     * @param array $roomAndServiceData
     * @return array
     */
    protected function getTotalsData($roomAndServiceData)
    {
        $objBookingDemand = new HotelBookingDemands();
        $totalDemandsPriceTE = (float)$objBookingDemand->getRoomTypeBookingExtraDemands(
            (int)$this->order->id, 0, 0, 0, 0, 1, 1, 0
        );
        $totalDemandsPriceTI = (float)$objBookingDemand->getRoomTypeBookingExtraDemands(
            (int)$this->order->id, 0, 0, 0, 0, 1, 1, 1
        );

        $idsOrderDetail = $this->getOrderDetailIds();

        $roomsCost = 0;
        $roomsCostTaxIncl = 0;
        $serviceProductsCost = 0;
        $serviceProductsCostTaxIncl = 0;
        $additionalServicesCost = 0;
        $additionalServicesCostTaxIncl = 0;
        $convenienceFee = 0;
        $convenienceFeeTaxIncl = 0;
        $productDiscountsTaxExcl = (float)$this->order->total_discounts_tax_excl;
        $productDiscountsTaxIncl = (float)$this->order->total_discounts_tax_incl;

        foreach ($this->order->getCartRules() as $cartRule) {
            if (!empty($cartRule['free_shipping'])) {
                $productDiscountsTaxExcl -= (float)$this->order->total_shipping_tax_excl;
                $productDiscountsTaxIncl -= (float)$this->order->total_shipping_tax_incl;
            }
        }

        if ($productDiscountsTaxExcl < 0) {
            $productDiscountsTaxExcl = 0;
        }
        if ($productDiscountsTaxIncl < 0) {
            $productDiscountsTaxIncl = 0;
        }

        if (!empty($idsOrderDetail)) {
            $roomsCost = (float)$this->order->getTotalProductsWithoutTaxes(
                false, true, null, null, null, $idsOrderDetail
            ) + (float)$this->order->getTotalProductsWithoutTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE,
                1, Product::PRICE_ADDITION_TYPE_WITH_ROOM, $idsOrderDetail
            );
            $roomsCostTaxIncl = (float)$this->order->getTotalProductsWithTaxes(
                false, true, null, null, null, $idsOrderDetail
            ) + (float)$this->order->getTotalProductsWithTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE,
                1, Product::PRICE_ADDITION_TYPE_WITH_ROOM, $idsOrderDetail
            );

            $serviceProductsCost = (float)$this->order->getTotalProductsWithoutTaxes(
                false, false, Product::SELLING_PREFERENCE_STANDALONE, false, false, $idsOrderDetail
            ) + (float)$this->order->getTotalProductsWithoutTaxes(
                false, false, Product::SELLING_PREFERENCE_HOTEL_STANDALONE, false, false, $idsOrderDetail
            );
            $serviceProductsCostTaxIncl = (float)$this->order->getTotalProductsWithTaxes(
                false, false, Product::SELLING_PREFERENCE_STANDALONE, false, false, $idsOrderDetail
            ) + (float)$this->order->getTotalProductsWithTaxes(
                false, false, Product::SELLING_PREFERENCE_HOTEL_STANDALONE, false, false, $idsOrderDetail
            );

            $additionalServicesCost = (float)$this->order->getTotalProductsWithoutTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE, 0, null, $idsOrderDetail
            ) + $totalDemandsPriceTE;
            $additionalServicesCostTaxIncl = (float)$this->order->getTotalProductsWithTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE, 0, null, $idsOrderDetail
            ) + $totalDemandsPriceTI;

            $convenienceFee = (float)$this->order->getTotalProductsWithoutTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE,
                1, Product::PRICE_ADDITION_TYPE_INDEPENDENT, $idsOrderDetail
            );
            $convenienceFeeTaxIncl = (float)$this->order->getTotalProductsWithTaxes(
                false, false, Product::SELLING_PREFERENCE_WITH_ROOM_TYPE,
                1, Product::PRICE_ADDITION_TYPE_INDEPENDENT, $idsOrderDetail
            );
        }

        $grandTotal = (float)$this->order->total_paid_tax_incl;
        $totalPaidReal = (float)$this->order->getTotalPaid();
        $totalWithoutDiscountTaxExcl = $roomsCost + $serviceProductsCost + $additionalServicesCost + $convenienceFee;
        $totalWithoutDiscountTaxIncl = $roomsCostTaxIncl + $serviceProductsCostTaxIncl + $additionalServicesCostTaxIncl + $convenienceFeeTaxIncl;
        $totalTaxes = $totalWithoutDiscountTaxIncl - $totalWithoutDiscountTaxExcl;
        if ($totalTaxes < 0) {
            $totalTaxes = 0;
        }

        $amountDue = $grandTotal - $totalPaidReal;
        if ($amountDue < 0) {
            $amountDue = 0;
        }

        return array(
            'total_rooms' => (int)$roomAndServiceData['total_rooms'],
            'total_room_nights' => (int)$roomAndServiceData['total_room_nights'],
            'total_guests' => (int)$roomAndServiceData['total_guests'],
            'rooms_cost' => $roomsCost,
            'room_price_tax_excl' => $roomsCost,
            'room_price_tax_incl' => $roomsCostTaxIncl,
            'service_products_price_tax_excl' => $serviceProductsCost,
            'service_products_price_tax_incl' => $serviceProductsCostTaxIncl,
            'additional_service_price_tax_excl' => $additionalServicesCost,
            'additional_service_price_tax_incl' => $additionalServicesCostTaxIncl,
            'extra_services_cost' => $additionalServicesCost + $serviceProductsCost,
            'convenience_fee' => $convenienceFee,
            'total_convenience_fee_te' => $convenienceFee,
            'total_convenience_fee_ti' => $convenienceFeeTaxIncl,
            'total_paid_tax_excl' => (float)$this->order->total_paid_tax_excl,
            'total_taxes' => $totalTaxes,
            'total_rooms_tax' => $totalTaxes,
            'extra_services_tax' => 0,
            'total_without_discount_te' => $totalWithoutDiscountTaxExcl,
            'total_without_discount_ti' => $totalWithoutDiscountTaxIncl,
            'total_tax_without_discount' => $totalTaxes,
            'product_discounts_tax_excl' => $productDiscountsTaxExcl,
            'product_discounts_tax_incl' => $productDiscountsTaxIncl,
            'grand_total' => $grandTotal,
            'total_paid' => $totalPaidReal,
            'total_paid_real' => $totalPaidReal,
            'total_paid_tax_incl' => $grandTotal,
            'amount_due' => Tools::ps_round($amountDue, _PS_PRICE_COMPUTE_PRECISION_, $this->order->round_mode),
        );
    }

    /**
     * Builds tax breakdown by product type, handling both order detail taxes and extra demand taxes separately.
     * Extracted: two-pass algorithm with per-type bucketing.
     *
     * @return array
     */
    protected function getTaxBreakdownData()
    {
        $breakdowns = array(
            'room_tax' => array(),
            'additional_services_tax' => array(),
            'convenience_fee_tax' => array(),
            'extra_demands_tax' => array(),
            'service_products_tax' => array(),
        );

        $orderDetails = OrderDetail::getList((int)$this->order->id);
        if (is_array($orderDetails)) {
            foreach ($orderDetails as $orderDetail) {
                $breakdownType = $this->getTaxBreakdownType($orderDetail);
                if (!$breakdownType) {
                    continue;
                }

                $taxes = OrderDetail::getTaxListStatic((int)$orderDetail['id_order_detail']);
                if (!$taxes) {
                    $taxAmount = (float)$orderDetail['total_price_tax_incl'] - (float)$orderDetail['total_price_tax_excl'];
                    if ($taxAmount <= 0) {
                        continue;
                    }
                    $taxes = array(array(
                        'id_tax' => 0,
                        'rate' => (float)$orderDetail['total_price_tax_excl'] > 0
                            ? (($taxAmount / (float)$orderDetail['total_price_tax_excl']) * 100) : 0,
                        'total_amount' => $taxAmount,
                    ));
                }

                foreach ($taxes as $taxDetail) {
                    $objTax = new Tax((int)$taxDetail['id_tax']);
                    $rate = Validate::isLoadedObject($objTax)
                        ? (float)$objTax->rate
                        : (isset($taxDetail['rate']) ? (float)$taxDetail['rate'] : 0);
                    $key = sprintf('%.3f', $rate);

                    if (!isset($breakdowns[$breakdownType][$key])) {
                        $breakdowns[$breakdownType][$key] = array(
                            'total_price_tax_excl' => 0,
                            'total_tax_excl' => 0,
                            'total_amount' => 0,
                            'id_tax' => (int)$taxDetail['id_tax'],
                            'rate' => sprintf('%.3f', $rate),
                            'name' => Validate::isLoadedObject($objTax)
                                ? $objTax->name[(int)$this->order->id_lang] : '',
                        );
                    }

                    $breakdowns[$breakdownType][$key]['total_price_tax_excl'] += (float)$orderDetail['total_price_tax_excl'];
                    $breakdowns[$breakdownType][$key]['total_tax_excl'] += (float)$orderDetail['total_price_tax_excl'];
                    $breakdowns[$breakdownType][$key]['total_amount'] += (float)$taxDetail['total_amount'];
                }
            }
        }

        $objBookingDemand = new HotelBookingDemands();
        $extraDemandTaxes = $objBookingDemand->getExtraDemandsTaxesDetails(
            (int)$this->order->id,
            $this->getOrderDetailIds()
        );

        if (is_array($extraDemandTaxes)) {
            foreach ($extraDemandTaxes as $taxDetail) {
                $rate = isset($taxDetail['rate']) ? (float)$taxDetail['rate'] : 0;
                $key = sprintf('%.3f', $rate);

                if (!isset($breakdowns['extra_demands_tax'][$key])) {
                    $breakdowns['extra_demands_tax'][$key] = array(
                        'total_price_tax_excl' => 0,
                        'total_tax_excl' => 0,
                        'total_amount' => 0,
                        'id_tax' => (int)$taxDetail['id_tax'],
                        'rate' => sprintf('%.3f', $rate),
                        'name' => isset($taxDetail['name']) ? $taxDetail['name'] : '',
                    );
                }

                $taxBase = isset($taxDetail['total_tax_base'])
                    ? (float)$taxDetail['total_tax_base']
                    : (float)$taxDetail['total_price_tax_excl'];
                $breakdowns['extra_demands_tax'][$key]['total_price_tax_excl'] += $taxBase;
                $breakdowns['extra_demands_tax'][$key]['total_tax_excl'] += $taxBase;
                $breakdowns['extra_demands_tax'][$key]['total_amount'] += (float)$taxDetail['total_amount'];
            }
        }

        foreach ($breakdowns as $type => &$breakdown) {
            if (empty($breakdown)) {
                unset($breakdowns[$type]);
                continue;
            }
            foreach ($breakdown as &$line) {
                $line['total_price_tax_excl'] = Tools::ps_round(
                    $line['total_price_tax_excl'],
                    _PS_PRICE_COMPUTE_PRECISION_,
                    $this->order->round_mode
                );
                $line['total_tax_excl'] = $line['total_price_tax_excl'];
                $line['total_amount'] = Tools::ps_round(
                    $line['total_amount'],
                    _PS_PRICE_COMPUTE_PRECISION_,
                    $this->order->round_mode
                );
            }
            unset($line);
        }
        unset($breakdown);

        return $breakdowns;
    }

    /**
     * Classifies an OrderDetail row into a tax breakdown bucket.
     * Extracted: complex multi-condition classification called inside a loop.
     *
     * @param array $orderDetail
     * @return string|false
     */
    protected function getTaxBreakdownType($orderDetail)
    {
        if (!empty($orderDetail['is_booking_product'])
            || ((int)$orderDetail['product_auto_add']
                && (int)$orderDetail['selling_preference_type'] === (int)Product::SELLING_PREFERENCE_WITH_ROOM_TYPE
                && (int)$orderDetail['product_price_addition_type'] === (int)Product::PRICE_ADDITION_TYPE_WITH_ROOM)
        ) {
            return 'room_tax';
        }

        if (!(int)$orderDetail['is_booking_product']
            && !(int)$orderDetail['product_auto_add']
            && (int)$orderDetail['selling_preference_type'] === (int)Product::SELLING_PREFERENCE_WITH_ROOM_TYPE
        ) {
            return 'additional_services_tax';
        }

        if (!(int)$orderDetail['is_booking_product']
            && (int)$orderDetail['product_auto_add']
            && (int)$orderDetail['selling_preference_type'] === (int)Product::SELLING_PREFERENCE_WITH_ROOM_TYPE
            && (int)$orderDetail['product_price_addition_type'] === (int)Product::PRICE_ADDITION_TYPE_INDEPENDENT
        ) {
            return 'convenience_fee_tax';
        }

        if (!(int)$orderDetail['is_booking_product']
            && ((int)$orderDetail['selling_preference_type'] === (int)Product::SELLING_PREFERENCE_STANDALONE
                || (int)$orderDetail['selling_preference_type'] === (int)Product::SELLING_PREFERENCE_HOTEL_STANDALONE)
        ) {
            return 'service_products_tax';
        }

        return false;
    }

    /**
     * Returns order detail IDs for this order.
     * Reused by getTotalsData() and getTaxBreakdownData().
     *
     * @return array
     */
    protected function getOrderDetailIds()
    {
        $orderDetails = OrderDetail::getList((int)$this->order->id);

        if (!is_array($orderDetails)) {
            return array();
        }

        return array_unique(array_map('intval', array_column($orderDetails, 'id_order_detail')));
    }

    /**
     * Formats a date for display; returns '--' for empty/zero dates.
     * Reused across getRoomAndServiceData and getRoomServiceData loops.
     *
     * @param string $date
     * @return string
     */
    protected function formatDisplayDate($date)
    {
        if (!$date || $date === '0000-00-00 00:00:00') {
            return '--';
        }

        return Tools::displayDate($date, null, false);
    }

    /**
     * Fetches and formats the hotel's cancellation/refund rules as HTML.
     *
     * @param int $idHotel
     * @return string
     */
    protected function getFormattedCancellationPolicy($idHotel)
    {
        if (!(int)$idHotel) {
            return '';
        }

        $objBranchRefundRules = new HotelBranchRefundRules();
        $refundRules = $objBranchRefundRules->getHotelRefundRules(
            (int)$idHotel, 0, 1, (int)$this->order->id_lang, 1
        );

        if (!is_array($refundRules) || empty($refundRules)) {
            return '';
        }

        $policyBlocks = array();
        foreach ($refundRules as $rule) {
            $ruleName = isset($rule['name']) ? trim(strip_tags($rule['name'])) : '';
            $ruleDescription = isset($rule['description']) ? trim($rule['description']) : '';

            if ($ruleName === '' && $ruleDescription === '') {
                continue;
            }

            $block = '';
            if ($ruleName !== '') {
                $block .= '<strong>'.Tools::safeOutput($ruleName).'</strong>';
            }
            if ($ruleDescription !== '') {
                if ($block !== '') {
                    $block .= ': ';
                }
                $block .= $ruleDescription;
            }
            $policyBlocks[] = $block;
        }

        if (empty($policyBlocks)) {
            return '';
        }

        return implode('<br /><br />', $policyBlocks);
    }

    /**
     * @return string
     */
    public function getBulkFilename()
    {
        return self::l('BookingVouchers').'.pdf';
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return sprintf(self::l('BOOKING VOUCHER - Refrence No. #%s.pdf'), $this->order->reference);
    }
}
