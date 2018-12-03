<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Control;

use SilverCart\Payment\Heidelpay\Model\Heidelpay;
 */
use Heidelpay\PhpPaymentApi\Response;

/**
 * Heidelpay payment method async / remote notification handler.
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 26.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayNotification extends SS_Object {

    /**
     * This method will be called by the distributoing script and receives the
     * paypal status message
     *
     * Diese Methode wird vom Verteilerscript aufgerufen und nimmt die Status-
     * meldungen von Paypal entgegen.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    public function process($paymentChannel) {
        $paymentMethod = SilvercartPaymentHeidelpay::get()->byID($paymentChannel);
        SilvercartPaymentHeidelpay::singleton()->Log('HeidelpayNotification', 'payment notification called.');
        
        if (!($paymentMethod instanceof SilvercartPaymentHeidelpay &&
              $paymentMethod->exists())) {
            // payment method not found
            SilvercartPaymentHeidelpay::singleton()->Log('HeidelpayNotification', 'ERROR: payment method for channel #' . $paymentChannel . ' not found.');
            SilvercartPaymentHeidelpay::singleton()->Log('HeidelpayNotification', '----');
            exit();
        }
        $paymentMethod->Log('HeidelpayNotification', 'payment notification for channel #' . $paymentChannel . ' ' . $paymentMethod->class . '.');
        
        $member            = null;
        $heidelpayResponse = new Response($_POST);
        $transactionStatus = SilvercartPaymentHeidelpayTransaction::STATUS_ERROR;
        $transactionID     = $heidelpayResponse->getIdentification()->getTransactionId();
        $transaction       = SilvercartPaymentHeidelpayTransaction::get_by_transaction_id($transactionID);
        if ($transaction instanceof SilvercartPaymentHeidelpayTransaction &&
            $transaction->exists()) {
            $member = $transaction->Member();
        } else {
            $paymentMethod->Log('HeidelpayNotification', 'ERROR: transaction #' . $transactionID . ' not found.');
            $paymentMethod->Log('HeidelpayNotification', '----');
            return;
        }
        
        $secretPass = $paymentMethod->getSecret($member);

        try {
            $heidelpayResponse->verifySecurityHash($secretPass, $transactionID);
        } catch (\Exception $e) {
            $paymentMethod->Log('HeidelpayNotification', 'ERROR: could not verify security hash.');
            $paymentMethod->Log('HeidelpayNotification', '-- code: ' . $e->getCode());
            $paymentMethod->Log('HeidelpayNotification', '-- message: ' . $e->getMessage());
            $paymentMethod->Log('HeidelpayNotification', '----');
            return;
        }

        if ($heidelpayResponse->isSuccess()) {
            /* save order and transaction result to your database */
            $paymentMethod->Log('HeidelpayNotification', 'SUCCESS: payment accepted.');
            $param             = '?success';
            $transactionStatus = SilvercartPaymentHeidelpayTransaction::STATUS_SUCCESS;
            if ($heidelpayResponse->isPending()) {
                $transactionStatus = SilvercartPaymentHeidelpayTransaction::STATUS_PENDING;
                $paymentMethod->Log('HeidelpayNotification', '-- PENDING: payment is successful, but pending.');
            }
        } elseif ($heidelpayResponse->isError()) {
            $error = $heidelpayResponse->getError();
            $param = '?errorMessage=' . urlencode(htmlspecialchars($error['message']));
            $paymentMethod->Log('HeidelpayNotification', 'ERROR: ' . $error);
        }
        
        $transaction->Status = $transactionStatus;
        $transaction->write();
        
        $link = SilvercartTools::PageByIdentifierCode('SilvercartCheckoutStep')->AbsoluteLink() . $param;
        print $link;
        exit();
    }
    
}
