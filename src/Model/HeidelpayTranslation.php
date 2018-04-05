<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Model;

use SilverCart\Dev\Tools;
use SilverCart\Model\Payment\PaymentMethodTranslation;
use SilverCart\Payment\Heidelpay\Model\Heidelpay;
 */

/**
 * Heidelpay payment method translation object.
 * Names are already prepared to work with SilverCart 4
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpayLanguage extends SilvercartPaymentMethodLanguage {
    
    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    private static $has_one = array(
        'SilvercartPaymentHeidelpay' => 'SilvercartPaymentHeidelpay'
    );
    
    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function singular_name() {
        SilvercartTools::singular_name_for($this);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function plural_name() {
        SilvercartTools::plural_name_for($this);
    }
    
}