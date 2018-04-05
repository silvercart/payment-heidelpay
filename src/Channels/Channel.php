<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Channels;
 */

/**
 * Heidelpay payment channel.
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Channels
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
abstract class SilvercartPaymentHeidelpayChannel {
    
    /**
     * Payment adapter.
     *
     * @var Heidelpay\PhpPaymentApi\PaymentMethods\PaymentMethodInterface 
     */
    protected $payment = null;
    
    /**
     * Payment method.
     *
     * @var SilvercartPaymentHeidelpay 
     */
    protected $paymentMethod = null;

    /**
     * Returns the payment transaction channel string.
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    abstract protected function getTransactionChannel();

    /**
     * Initializes and returns the payment object.
     * 
     * @return Heidelpay\PhpPaymentApi\PaymentMethods\PaymentMethodInterface
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    abstract protected function initPayment();

    /**
     * Hook to process payment specific routines right BEFORE the order is placed.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    abstract public function processPaymentBeforeOrder();

    /**
     * Hook to process payment specific routines right AFTER the order is placed.
     *
     * @param SilvercartOrder $order the order object
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    abstract public function processPaymentAfterOrder($order);
    
    /**
     * Hook to process payment specific routines right AFTER a payment provider jumped back into the
     * checkout and BEFORE the order is created.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    abstract public function processReturnJumpFromPaymentProvider();
    
    /**
     * Hook to get a text message to display AFTER order creation.
     *
     * @param SilvercartOrder $order the order object
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    abstract public function processPaymentConfirmationText($order);

    /**
     * Returns the payment object.
     * 
     * @return Heidelpay\PhpPaymentApi\PaymentMethods\PaymentMethodInterface
     */
    public function getPayment() {
        if (is_null($this->payment)) {
            $this->initPayment();
        }
        return $this->payment;
    }

    /**
     * Returns the payment method object.
     * 
     * @return SilvercartPaymentHeidelpay
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }
    
    /**
     * Sets the payment method object.
     * 
     * @param SilvercartPaymentHeidelpay $paymentMethod Payment method
     * 
     * @return void
     */
    public function setPaymentMethod(SilvercartPaymentHeidelpay $paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Returns the URL to execute the payment externally.
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function getPaymentFormUrl() {
        return $this->getPayment()->getResponse()->getPaymentFormUrl();
    }
    
    /**
     * Returns the order confirmation submit button title.
     * 
     * @return string
     */
    public function getOrderConfirmationSubmitButtonTitle() {
        return _t(self::class . '.OrderConfirmationSubmitButtonTitle', $this->getPaymentMethod()->fieldLabel('OrderConfirmationSubmitButtonTitle'));
    }
    
    /**
     * Initializes the transaction with the payment and customer basic data.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    protected function initTransaction() {
        $payment       = $this->getPayment();
        $paymentMethod = $this->getPaymentMethod();
        $request       = $payment->getRequest();
        
        $customer        = SilvercartCustomer::currentUser();
        $shoppingCart    = $paymentMethod->getShoppingCart();
        $shippingAddress = $paymentMethod->getShippingAddress();
        
        $totalAmount = round((float) $shoppingCart->getAmountTotal()->getAmount(), 2);
        $currency    = $shoppingCart->getAmountTotal()->getCurrency();
        
        $request->authentification(
            $paymentMethod->getAPISenderID(),
            $paymentMethod->getAPILogin(),
            $paymentMethod->getAPIPassword(),
            $this->getTransactionChannel(),
            $paymentMethod->getAPISandboxMode()
        );
        
        $request->async(
                strtoupper(i18n::get_lang_from_locale(i18n::get_locale())),
                $paymentMethod->getNotificationLink()
        );
        
        $request->customerAddress(
                $shippingAddress->FirstName,
                $shippingAddress->Surname,
                $shippingAddress->Company,
                $customer->CustomerNumber,
                $shippingAddress->Street,
                $shippingAddress->State,
                $shippingAddress->Postcode,
                $shippingAddress->City,
                $shippingAddress->SilvercartCountry()->ISO2,
                $customer->Email
        );
        
        $request->basketData(
            $paymentMethod->getTransactionID(),
            $totalAmount,
            $currency,
            $paymentMethod->getSecret()
        );
    }
    
}