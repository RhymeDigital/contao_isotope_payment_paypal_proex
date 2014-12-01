<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Isotope_brand
 * @link    https://contao.org
 
 * PHP version 5
 * @copyright  360fusion  2014
 * @author     Darrell Martin <darrell@360fusion.co.uk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */



/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope_payment_paypal_proex/library');




/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
   'iso_payment_paypalpaymentspro'               => 'system/modules/isotope_payment_paypal_proex/templates/payment',
   
));


