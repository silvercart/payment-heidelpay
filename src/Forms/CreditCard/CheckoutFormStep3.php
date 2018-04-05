<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Forms;
 */

/**
 * Pament step three.
 * E.g. used to handle a payment providers response after executing the payment.
 *
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayCreditcardCheckoutFormStep3 extends SilvercartCheckoutFormStepDefaultOrderConfirmation {

    /**
     * Render this step with the default template
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function init() {
        $order         = $this->getOrder();
        $paymentMethod = $this->getPaymentMethod();
        
        return $this->customise([
            'PaymentConfirmationText' => $paymentMethod->processPaymentConfirmationText($order),
        ])->renderWith('SilvercartCheckoutFormStepDefaultOrderConfirmation');
    }
}

