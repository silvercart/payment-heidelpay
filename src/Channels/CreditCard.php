<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Channels;

use SilverCart\Payment\Heidelpay\Channels\Channel;
 */

use Heidelpay\PhpPaymentApi\PaymentMethods\CreditCardPaymentMethod;

/**
 * Heidelpay payment channel for credit card payments.
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Channels
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayCreditCard extends SilvercartPaymentHeidelpayChannel {

    /**
     * Returns the payment transaction channel string.
     * Credit card channels:
     *  - 3D Secure:    31HA07BC8142C5A171749A60D979B6E4
     *  - no 3D Secure: 31HA07BC8142C5A171744F3D6D155865
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    protected function getTransactionChannel() {
        return '31HA07BC8142C5A171749A60D979B6E4';
    }
    
    /**
     * Returns the payment object.
     * 
     * @return Heidelpay\PhpPaymentApi\PaymentMethods\CreditCardPaymentMethod
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function initPayment() {
        $this->payment = new CreditCardPaymentMethod();
    }

    /**
     * Returns the payment object.
     * 
     * @return Heidelpay\PhpPaymentApi\PaymentMethods\CreditCardPaymentMethod
     */
    public function getPayment() {
        return parent::getPayment();
    }

    /**
     * Hook to process payment specific routines right BEFORE the order is placed.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    public function processPaymentBeforeOrder() {
        $this->initTransaction();
        $transactionStatus = SilvercartPaymentHeidelpayTransaction::STATUS_REGISTRATION;
        $transactionID     = $this->getPayment()->getRequest()->getIdentification()->getTransactionId();
        $transaction       = SilvercartPaymentHeidelpayTransaction::get_by_transaction_id($transactionID, SilvercartCustomer::currentUser());
        
        if ($transaction instanceof SilvercartPaymentHeidelpayTransaction &&
            $transaction->exists() &&
            $transaction->Status != $transactionStatus) {
            
            $ctrl = $this->getPaymentMethod()->getController();
            $ctrl->addCompletedStep($ctrl->getCurrentStep());
            $ctrl->setCurrentStep($ctrl->getNextStep());
            $ctrl->redirect($ctrl->Link(), 302);
        } else {
            $paymentFrameOrigin = Director::absoluteBaseURL();
            if (strpos(strrev($paymentFrameOrigin), '/') === 0) {
                $paymentFrameOrigin = substr($paymentFrameOrigin, 0, -1);
            }

            $this->getPayment()->registration(
                $paymentFrameOrigin,
                'FALSE',
                $this->getPaymentMethod()->absoluteThemedCSSFileURL('Heidelpay')
            );
            
            if (!$this->getPayment()->getResponse()->isSuccess()) {
                print_r($this->getPayment()->getResponse()->getError());
                exit();
            }

            SilvercartPaymentHeidelpayTransaction::create_transaction($transactionID, $transactionStatus);
        }
    }

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
    public function processPaymentAfterOrder($order) {
        $transactionStatus = SilvercartPaymentHeidelpayTransaction::STATUS_CAPTURED;
        $transactionID     = $this->getPaymentMethod()->getTransactionID();
        $transaction       = SilvercartPaymentHeidelpayTransaction::get_by_transaction_id($transactionID, SilvercartCustomer::currentUser());
        
        if ($transaction instanceof SilvercartPaymentHeidelpayTransaction &&
            $transaction->exists() &&
            $transaction->Status != $transactionStatus) {
            
            $this->getPayment()->capture($transactionID);
            
            $transaction->Status  = $transactionStatus;
            $transaction->OrderID = $order->ID;
            $transaction->write();
        }

        $ctrl = $this->getPaymentMethod()->getController();
        $ctrl->addCompletedStep();
        $ctrl->NextStep();
    }
    
    /**
     * Hook to process payment specific routines right AFTER a payment provider jumped back into the
     * checkout and BEFORE the order is created.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    public function processReturnJumpFromPaymentProvider() {
        // Nothing to do here for credit card payment.
    }
    
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
    public function processPaymentConfirmationText($order) {
        // Nothing to do here for credit card payment.
    }
    
}