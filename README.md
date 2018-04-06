# SilverCart Payment Heidelpay
Heidelpay payment module for the SilverCart E-Commerce System

## Maintainer Contact

* Sebastian Diel <sdiel@pixeltricks.de>
* Ramon Kupper <rkupper@pixeltricks.de>

## Requirements
* SilverCart 3.4 (or higher, < 4.0)

## Basic installation from scratch with composer in existing SilverCart installations
1. composer require silvercart/payment-heidelpay

## Basic installation from scratch with composer
1. composer create-project silverstripe/installer silvercart_demo 3.6
2. cd silvercart_demo 
3. composer require silvercart/payment-heidelpay
4. change the class definitions in mysite/code/Page.php to: 
   class Page extends SilvercartPage 
   class Page_Controller extends SilvercartPage_Controller 
5. open website in your browser and finish SilverStripe installation process with: 
   Theme selection: empty theme (this is the only necessary setting for SilverCart) 
6. Open CMS backend 
7. Settings -> Email Settings: enter valid Email sender 
8. Settings -> Add Example data 
   Add Example Data (this might take a few minutes!) 
   Add Example Configuration

## Summary
SilverCart is an Open Source E-Commerce module for the CMS Framework SilverStripe.

For more information about the SilverCart visit http://www.silvercart.org/about/

## License

See LICENSE
