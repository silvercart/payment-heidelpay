<?php

/**
 * Namespace preparation for SilverCart 4 compatibility.
 * 
namespace SilverCart\Payment\Heidelpay\Model;

use SilverCart\Model\Payment\PaymentMethod;
use SilverCart\Model\ShopEmail;
use SilverCart\Model\Translation\TranslationTools;
use SilverCart\Payment\Heidelpay\Model\HeidelpayTranslation;
 */

/**
 * Heidelpay payment method object to use in SilverCart checkout.
 * 
 * @package SilverCart
 * @subpackage Payment_Heidelpay_Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @copyright 2018 pixeltricks GmbH
 * @since 22.03.2018
 * @license see license file in modules root directory
 */
class SilvercartPaymentHeidelpay extends SilvercartPaymentMethod {

    /**
     * Indicates whether a payment module has multiple payment channels or not.
     *
     * @var bool
     */
    public static $has_multiple_payment_channels = true;
    
    /**
     * A list of possible payment channels.
     *
     * @var array
     */
    public static $possible_payment_channels = [
        'creditcard' => 'CreditCard',
    ];
    
    /**
     * classes attributes
     *
     * @var array
     */
    private static $db = [
        'APISenderID'    => 'Varchar',
        'APILogin'       => 'Varchar',
        'APIPassword'    => 'Varchar',
        'PaymentChannel' => 'Enum("creditcard","creditcard")',
    ];

    /**
     * 1:n relationships.
     *
     * @var array
     */
    private static $has_many = [
        'SilvercartPaymentHeidelpayLanguages' => 'SilvercartPaymentHeidelpayLanguage',
    ];

    /**
     * Casted attributes.
     *
     * @var array
     */
    private static $casting = [
        'APISandboxMode' => 'Boolean',
    ];

    /**
     * module name to be shown in backend interface
     *
     * @var string
     */
    protected $moduleName = 'Heidelpay';

    /**
     * Current payment channel
     *
     * @var SilvercartPaymentHeidelpayChannel
     */
    protected $channel = null;

    /**
     * Current payment transaction ID
     *
     * @var string
     */
    protected $transactionID = null;
    
    /**
     * API credentials for dev mode.
     *
     * @var array
     */
    private static $dev_api_credentials = [
        'SenderID'       => '31HA07BC8142C5A171745D00AD63D182',
        'Login'          => '31ha07bc8142c5a171744e5aef11ffd3',
        'Password'       => '93167DE7',
        'SandboxMode'    => 'yes',
    ];
    
    /**
     * API channels for dev mode.
     *
     * @var array
     */
    private static $dev_api_channels = [
        'creditcard' => [
            '3DSecure' => [
                'Channel'    => '31HA07BC8142C5A171749A60D979B6E4',
                'Currencies' => ['EUR', 'USD', 'GBP', 'CZK', 'CHF', 'SEK'],
            ],
            'No3DSecure' => [
                'Channel'    => '31HA07BC8142C5A171744F3D6D155865',
                'Currencies' => ['EUR', 'USD', 'GBP', 'CZK', 'CHF', 'SEK'],
            ],
        ],
    ];
    
    /**
     * API example data for dev mode.
     *
     * @var array
     */
    private static $dev_api_example_data = [
        'creditcard' => [
            'MasterCard' => [
                'Number'   => '5453010000059543',
                'Expires'  => 'AnyDateInFuture',
                'CVV'      => '123',
                'Password' => 'secret3',
            ],
            'VISA' => [
                'Number'   => '4711100000000000',
                'Expires'  => 'AnyDateInFuture',
                'CVV'      => '123',
                'Password' => 'secret3',
            ],
        ],
    ];

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),
                [
                    'EnterCreditCardData'                 => _t(self::class . '.EnterCreditCardData', 'Enter credit card data'),
                    'OrderConfirmationSubmitButtonTitle'  => _t(self::class . '.OrderConfirmationSubmitButtonTitle', 'Enter credit card data'),
                    'SilvercartPaymentHeidelpayLanguages' => SilvercartPaymentHeidelpayLanguage::singleton()->plural_name(),
                ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }

    /**
     * input fields for editing
     *
     * @param mixed $params optional
     *
     * @return FieldList
     */
    public function getCMSFields($params = null) {
        $fields = parent::getCMSFieldsForModules($params);
        
        $channelField = new ReadonlyField('DisplayPaymentChannel', $this->fieldLabel('PaymentChannel'), $this->getPaymentChannelName($this->PaymentChannel));
        $fields->addFieldToTab('Root.Basic', $channelField, 'mode');
        
        $translations = new GridField(
                'SilvercartPaymentHeidelpayLanguages',
                $this->fieldLabel('SilvercartPaymentHeidelpayLanguages'),
                $this->SilvercartPaymentHeidelpayLanguages(),
                SilvercartGridFieldConfig_ExclusiveRelationEditor::create()
        );
        $fields->addFieldToTab('Root.Translations', $translations);

        return $fields;
    }

    /***********************************************************************************************
     ***********************************************************************************************
     **                                                                                           ** 
     **                             Payment method specific section.                              ** 
     **                                                                                           ** 
     ***********************************************************************************************
     **********************************************************************************************/
    
    /**
     * Returns the payment channel.
     * 
     * @return SilvercartPaymentHeidelpayChannel
     */
    public function getChannel() {
        if (is_null($this->channel)) {
            $channelKey    = $this->PaymentChannel;
            $channels      = $this->config()->get('possible_payment_channels');
            $channelName   = $channels[$channelKey];
            //$className   = "\\SilverCart\\Payment\\Heidelpay\\Channels\\" . $channelName;
            $className     = "SilvercartPaymentHeidelpay" . $channelName;
            $this->channel = singleton($className);
            $this->channel->setPaymentMethod($this);
        }
        return $this->channel;
    }
    
    /**
     * Returns the API sender ID dependent on dev/live mode.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.03.2018
     */
    public function getAPISenderID() {
        $apiSenderID = '';
        if ($this->mode == 'Live') {
            $apiSenderID = $this->getField('APISenderID');
        } else {
            $devCredentials = $this->config()->get('dev_api_credentials');
            $apiSenderID    = $devCredentials['SenderID'];
        }
        return $apiSenderID;
    }
    
    /**
     * Returns the API login dependent on dev/live mode.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.03.2018
     */
    public function getAPILogin() {
        $apiLogin = '';
        if ($this->mode == 'Live') {
            $apiLogin = $this->getField('APILogin');
        } else {
            $devCredentials = $this->config()->get('dev_api_credentials');
            $apiLogin       = $devCredentials['Login'];
        }
        return $apiLogin;
    }
    
    /**
     * Returns the API password dependent on dev/live mode.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.03.2018
     */
    public function getAPIPassword() {
        $apiPassword = '';
        if ($this->mode == 'Live') {
            $apiPassword = $this->getField('APIPassword');
        } else {
            $devCredentials = $this->config()->get('dev_api_credentials');
            $apiPassword    = $devCredentials['Password'];
        }
        return $apiPassword;
    }
    
    /**
     * Returns the API sandbox mode dependent on dev/live mode.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.03.2018
     */
    public function getAPISandboxMode() {
        $apiSandboxMode = true;
        if ($this->mode == 'Live') {
            $apiSandboxMode = false;
        }
        return $apiSandboxMode;
    }
    
    /**
     * Returns the shop identifier.
     * 
     * @param Member $member Member
     * 
     * @return string
     */
    public function getTransactionID($member = null) {
        if (is_null($this->transactionID)) {
            if (is_null($member)) {
                $member = SilvercartCustomer::currentUser();
            }

            $transactionIDBase = implode('-', [
                $member->ID,
                $member->getCart()->ID,
                str_replace([' ', ':'], '-', $member->getCart()->SilvercartShoppingCartPositions()->max('LastEdited')),
            ]);
            $this->transactionID = $transactionIDBase;
        }
        return $this->transactionID;
    }
    
    /**
     * Returns the transaction secret.
     * 
     * @param Member $member Member
     * 
     * @return string
     */
    public function getSecret($member = null) {
        if (is_null($member)) {
            $member = SilvercartCustomer::currentUser();
        }
        $secretBase = implode('-', [
            $member->ID,
            $member->getCart()->ID,
            str_replace([' ', ':'], '-', $member->getCart()->SilvercartShoppingCartPositions()->max('LastEdited')),
        ]);
        $secret = md5($secretBase) . '-' . sha1($secretBase);
        
        return $secret;
    }

    /***********************************************************************************************
     ***********************************************************************************************
     **                                                                                           ** 
     ** Payment processing section. SilverCart checkout will call these methods:                  ** 
     **                                                                                           ** 
     **     - processPaymentBeforeOrder                                                           ** 
     **     - processPaymentAfterOrder                                                            ** 
     **     - processReturnJumpFromPaymentProvider                                                ** 
     **     - processPaymentConfirmationText                                                      ** 
     **                                                                                           ** 
     ***********************************************************************************************
     **********************************************************************************************/

    /**
     * Hook to process payment specific routines right BEFORE the order is placed.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function processPaymentBeforeOrder() {
        $this->getChannel()->processPaymentBeforeOrder();
    }

    /**
     * Hook to process payment specific routines right AFTER the order is placed.
     *
     * @param SilvercartOrder $orderObj the order object
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function processPaymentAfterOrder($orderObj) {
        $this->getChannel()->processPaymentAfterOrder($orderObj);
    }
    
    /**
     * Hook to process payment specific routines right AFTER a payment provider jumped back into the
     * checkout and BEFORE the order is created.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function processReturnJumpFromPaymentProvider() {
        $this->getChannel()->processReturnJumpFromPaymentProvider();
    }
    
    /**
     * display a text message after order creation
     *
     * @param SilvercartOrder $orderObj the order object
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2018
     */
    public function processPaymentConfirmationText($orderObj) {
        return $this->getChannel()->processPaymentConfirmationText($orderObj);
    }

    /***********************************************************************************************
     ***********************************************************************************************
     **                                                                                           ** 
     **                                 General helper functions                                  ** 
     **                                                                                           ** 
     ***********************************************************************************************
     **********************************************************************************************/

    /**
     * Set the title for the submit button on the order confirmation step.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 07.04.2011
     */
    public function getOrderConfirmationSubmitButtonTitle() {
        return $this->getChannel()->getOrderConfirmationSubmitButtonTitle();
    }
    
    /**
     * Returns the relative URL to the CSS file with the given name.
     * 
     * @param string $name CSS file name
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
	public function themedCSSFileURL($name) {
        $module     = HEIDELPAY_MODULE_NAME;
		$theme      = SSViewer::get_theme_folder();
		$project    = project();
		$absbase    = BASE_PATH . DIRECTORY_SEPARATOR;
		$abstheme   = $absbase . $theme;
		$absproject = $absbase . $project;
		$css        = "/css/$name.css";
        
		if (file_exists($absproject . $css)) {
			return $project . $css;
		} elseif($module && file_exists($abstheme . '_' . $module.$css)) {
			return $theme . '_' . $module . $css;
		} elseif(file_exists($abstheme . $css)) {
			return $theme . $css;
		} elseif($module) {
			return $module . '/client' . $css;
		}
	}
    
    /**
     * Returns the absolute URL to the CSS file with the given name.
     * 
     * @param string $name CSS file name
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.03.2018
     */
    public function absoluteThemedCSSFileURL($name) {
        return Director::absoluteURL($this->themedCSSFileURL($name));
    }
    
}