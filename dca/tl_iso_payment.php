<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 
 * PHP version 5
 * @copyright  360fusion  2014
 * @author     Darrell Martin <darrell@360fusion.co.uk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 
 */




/**
 * Add palettes to tl_iso_payment
 */
 
array_insert($GLOBALS['TL_DCA']['tl_iso_payment']['palettes'], 1, array 
(
   'paypal_express'  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{paypal_api_legend},paypal_api_username,paypal_api_password,paypal_api_signature;{paypal_gateway_legend},paypal_standard,paypal_payment_type,paypal_currency_code,paypal_environment;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',  
));


array_insert($GLOBALS['TL_DCA']['tl_iso_payment']['palettes'], 2, array 
(
   'paypal_payments_pro'  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{paypal_api_legend},paypal_api_username,paypal_api_password,paypal_api_signature;{paypal_gateway_legend},use_cc_billing,paypal_standard,paypal_allowedcc_types,paypal_payment_type,paypal_currency_code,paypal_environment;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',  
));



/**
 * Add fields to tl_iso_payment
 */
 

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_standard'] = array
(
			'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_standard'],
			'exclude'       => true,
			'inputType'     => 'text',
			'eval'          => array('mandatory'=>false, 'tl_class'=>'w10'),
			'sql'						=> "varchar(255) NOT NULL default ''",
);
 
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_identity_token'] = array
(
				'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_identity_token'],
				'exclude'       => true,
				'inputType'     => 'text',
				'eval'          => array('mandatory'=>false, 'tl_class'=>'w10'),
				'sql'						=> "varchar(255) NOT NULL default ''",
);
			
	
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_currency_code'] = array
(
				'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_currency_code'],
				'exclude'       => true,
				'inputType'     => 'select',
				'options'       => array('AUD', 'CAD', 'CHF' , 'CZK' , 'DKK' , 'EUR' , 'GBP' , 'HKD' , 'HUF' , 'JPY' , 'NOK' , 'NZD' , 'PLN' , 'SEK' , 'SGD' , 'USD'),
				'reference'     => &$GLOBALS['TL_LANG']['tl_iso_payment'],
				'sql'						=> "varchar(3) NOT NULL default ''",
);
			
			
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_api_username'] = array
(
		'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_username'],
		'exclude'       => true,
		'inputType'     => 'text',
		'eval'          => array('mandatory'=>true, 'tl_class'=>'w50'),
		'sql'						=> "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_api_password'] = array
(
		'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_password'],
		'exclude'       => true,
		'inputType'     => 'text',
		'eval'          => array('mandatory'=>true, 'tl_class'=>'w20'),
				'sql'						=> "varchar(255) NOT NULL default ''",
);
	
	
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_api_signature'] = array
(
		'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_api_signature'],
		'exclude'       => true,
		'inputType'     => 'text',
		'eval'          => array('mandatory'=>true, 'tl_class'=>'w60'),
		'sql'						=> "varchar(255) NOT NULL default ''",
);
	
	
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_payment_type'] = array
(
		'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_payment_type'],
		'exclude'       => true,
		'inputType'     => 'select',
		'options'       => array('Sale', 'Authorization', 'Order'),
		'reference'     => &$GLOBALS['TL_LANG']['tl_iso_payment'],
		'sql'						=> "varchar(20) NOT NULL default ''",
);


$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_environment'] = array
(
			'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_environment'],
			'exclude'       => true,
			'inputType'     => 'select',
			'options'       => array('sandbox', 'live'),
			'reference'     => &$GLOBALS['TL_LANG']['tl_iso_payment'],
			'sql'						=> "varchar(20) NOT NULL default ''",
);


$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['paypal_allowedcc_types'] = array
(
			'label'         => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_allowedcc_types'],
			'exclude'       => true,
			'inputType'     => 'checkboxWizard',
			'options'       => array('Mastercard', 'Visa', 'American Express' , 'Maestro', 'Discover'),
			'reference'     => &$GLOBALS['TL_LANG']['tl_iso_payment'],
			'eval'             => array('multiple'=>true),
			'sql'						=> "blob NULL",
);


  
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['use_cc_billing'] = array
(
	'label'         					=> &$GLOBALS['TL_LANG']['tl_iso_payment']['use_cc_billing'],
	'exclude'                 => true,
	'default'                 => '',
	'inputType'               => 'checkbox',
	'filter'                  => true,
	'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w90'),
	'sql'											=> "int(1) NOT NULL default '0'"
);
