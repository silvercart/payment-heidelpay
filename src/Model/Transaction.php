<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Model;

 */

/**
 * Heidelpay payment transaction object to use in SilverCart checkout.
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 04.04.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayTransaction extends DataObject {
    
    const STATUS_REGISTRATION = 'registration';
    const STATUS_SUCCESS      = 'success';
    const STATUS_CAPTURED     = 'captured';
    const STATUS_FINISHED     = 'finished';
    const STATUS_PENDING      = 'pending';
    const STATUS_ERROR        = 'error';
    
    /**
     * DB attributes
     *
     * @var array
     */
    private static $db = [
        'TransactionID' => 'Varchar',
        'Status'        => 'Varchar',
    ];
    
    /**
     * Has one relations
     *
     * @var array
     */
    private static $has_one = [
        'Member' => 'Member',
        'Order'  => 'SilvercartOrder',
    ];

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),
                [
                    'TransactionID' => _t(self::class . '.TransactionID', 'Transaction ID'),
                    'Status'        => _t(self::class . '.Status', 'Status'),
                    'Member'        => Member::singleton()->singular_name(),
                    'Order'         => SilvercartOrder::singleton()->singular_name(),
                ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Returns the transaction with the given ID and (optional) Member.
     * 
     * @param string $transactionID Transaction ID
     * @param Member $member        Member
     * 
     * @return SilvercartPaymentHeidelpayTransaction
     */
    public static function get_by_transaction_id($transactionID, $member = null) {
        if (!is_null($member)) {
            $transaction = SilvercartPaymentHeidelpayTransaction::get()->filter([
                'MemberID'      => $member->ID,
                'TransactionID' => $transactionID,
            ])->first();
        } else {
            $transaction = SilvercartPaymentHeidelpayTransaction::get()->filter([
                'TransactionID' => $transactionID,
            ])->first();
        }
        return $transaction;
    }
    
    /**
     * Creates (if not exists) and returns the transaction with the given ID, status and (optional) 
     * Member.
     * 
     * @param string $transactionID     Transaction ID
     * @param string $transactionStatus Transaction status (for creation only)
     * @param Member $member            Member
     * 
     * @return \SilvercartPaymentHeidelpayTransaction
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2018
     */
    public static function create_transaction($transactionID, $transactionStatus, $member = null) {
        if (is_null($member)) {
            $member = SilvercartCustomer::currentUser();
        }
        
        $transaction = self::get_by_transaction_id($transactionID, $member);
        
        if (!($transaction instanceof SilvercartPaymentHeidelpayTransaction) ||
            !$transaction->exists()) {
            $transaction = new SilvercartPaymentHeidelpayTransaction();
            $transaction->MemberID      = $member->ID;
            $transaction->TransactionID = $transactionID;
            $transaction->Status        = $transactionStatus;
            $transaction->write();
        }
        
        return $transaction;
    }
    
}
