<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Fields
 */



$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_username']   = array('API username', 'Link to the PayPal API username.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_password']   = array('API password', 'Link to the PayPal API password.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_signature']  = array('API signature', 'Link to the PayPal signature.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_payment_type'] = array('Payment type', 'Determine when to charge the buyer\'s PayPal or credit card account.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_standard']	= array('PayPal account', 'Enter the Paypal account to use to accept payments.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_identity_token']  = array('Identity Token', 'Link to the payments data transfer identity token for IPN.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_currency_code'] = array('Currency code', 'The 3 alpha currency code that represents the currency used for the payment.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_environment'] = array('Environment', 'Select whether this is a live or test environment.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_allowedcc_types']	= array('Allowed credit cards', 'Select allowed credit cards. NOTE: For UK, only Maestro, MasterCard, Discover, and Visa are allowable');
$GLOBALS['TL_LANG']['tl_iso_payment']['use_cc_billing']	 = array('Use billing address fields for credit cards',  'Show billing address fields');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['Sale'] = 'Sale';
$GLOBALS['TL_LANG']['tl_iso_payment']['Authorization'] = 'Authorization';
$GLOBALS['TL_LANG']['tl_iso_payment']['Order'] = 'Order';

$GLOBALS['TL_LANG']['tl_iso_payment']['AUD'] = 'Australian Dollar [AUD]';
$GLOBALS['TL_LANG']['tl_iso_payment']['CAD'] = 'Canadian Dollar [CAD]';
$GLOBALS['TL_LANG']['tl_iso_payment']['CHF'] = 'Swiss Franc [CHF]';
$GLOBALS['TL_LANG']['tl_iso_payment']['CZK'] = 'Czech Koruna [CZK]';
$GLOBALS['TL_LANG']['tl_iso_payment']['DKK'] = 'Danish Krone [DKK]';
$GLOBALS['TL_LANG']['tl_iso_payment']['EUR'] = 'Euro [EUR]';
$GLOBALS['TL_LANG']['tl_iso_payment']['GBP'] = 'Pound Sterling [GBP]';
$GLOBALS['TL_LANG']['tl_iso_payment']['HKD'] = 'Hong Kong Dollar [HKD]';
$GLOBALS['TL_LANG']['tl_iso_payment']['HUF'] = 'Hungarian Forint [HUF]';
$GLOBALS['TL_LANG']['tl_iso_payment']['JPY'] = 'Japanese Yen [JPY]';
$GLOBALS['TL_LANG']['tl_iso_payment']['NOK'] = 'Norwegian Krone [NOK]';
$GLOBALS['TL_LANG']['tl_iso_payment']['NZD'] = 'New Zealand Dollar [NZD]';
$GLOBALS['TL_LANG']['tl_iso_payment']['PLN'] = 'Polish Zloty [PLN]';
$GLOBALS['TL_LANG']['tl_iso_payment']['SEK'] = 'Swedish Krona [SEK]';
$GLOBALS['TL_LANG']['tl_iso_payment']['SGD'] = 'Singapore Dollar [SGD]';
$GLOBALS['TL_LANG']['tl_iso_payment']['USD'] = 'U.S Dollar [USD]';

$GLOBALS['TL_LANG']['tl_iso_payment']['live'] = 'Live';
$GLOBALS['TL_LANG']['tl_iso_payment']['sandbox'] = 'Sandbox (testing)';


$GLOBALS['TL_LANG']['tl_iso_payment']['MasterCard']					= 'MasterCard';
$GLOBALS['TL_LANG']['tl_iso_payment']['Visa']								= 'Visa';
$GLOBALS['TL_LANG']['tl_iso_payment']['American Express']		= 'American Express';
$GLOBALS['TL_LANG']['tl_iso_payment']['Discover']						= 'Discover';
$GLOBALS['TL_LANG']['tl_iso_payment']['Maestro']						= 'Maestro';




/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_gateway_legend']		= 'Payment Gateway Configuration';
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_legend'] = 'Paypal API credentials';
 

