<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Forms;

use SilverCart\Payment\Heidelpay\Model\Heidelpay;
 */

/**
 * Payment step one to initialize the payment.
 * Used to establish a connection / create a session with the payment provider.
 *
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayCreditcardCheckoutFormStep1 extends SilvercartCheckoutFormStepPaymentInit {

    /**
     * Here we set some preferences.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function preferences() {
        parent::preferences();

        $this->preferences['stepTitle']     = SilvercartPaymentHeidelpay::singleton()->fieldLabel('EnterCreditCardData');
        $this->preferences['stepIsVisible'] = true;
    }

    /**
     * Process the current step
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function process() {
        if (!parent::process()) {
            return $this->renderWith('SilvercartCheckoutFormStepPaymentError');
        }
        
        $checkoutLink  = $this->getController()->Link();
        $paymentMethod = $this->getPaymentMethod();
        
        $paymentMethod->setCancelLink(Director::absoluteURL($checkoutLink) . 'GotoStep/4');
        $paymentMethod->setReturnLink(Director::absoluteURL($checkoutLink));
        $paymentMethod->processPaymentBeforeOrder();
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
        $paymentMethod = $this->getPaymentMethod();
        return $paymentMethod->getChannel()->getPayment()->getResponse()->getPaymentFormUrl();
    }
}