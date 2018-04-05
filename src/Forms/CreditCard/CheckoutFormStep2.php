<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Forms;
 */

/**
 * Pament step two.
 * E.g. used to handle a payment providers response after executing the payment.
 *
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayCreditcardCheckoutFormStep2 extends SilvercartCheckoutFormStepProcessOrder {

    /**
     * Process the current step
     *
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function process() {
        $paymentMethod = $this->getPaymentMethod();
        $paymentMethod->getTransactionID();
        
        if (!parent::process()) {
            return $this->renderWith('SilvercartCheckoutFormStepPaymentError');
        }
        
        $paymentMethod->processPaymentAfterOrder($this->getOrder());
    }
}

