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

namespace Isotope\Model\Payment;

use Isotope\Model\Payment;
use Haste\Http\Response\Response;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


/**
 * Class PayPalPaymentsPro
 *
 * PHP version 5
 * @copyright  360fusion  2014
 * @author     Darrell Martin <darrell@360fusion.co.uk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 
 */
class PayPalPaymentsPro extends Payment implements IsotopePayment
{

   /**
     * Process payment on checkout page.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
				

			if ($_SESSION['nvpResArray']['ACK'] == "Success") {
		
            $arrPayment 								= deserialize($objOrder->payment_data, true);
            $arrPayment['POSTSALE'][] 	= $_POST;
            $arrPayment['status'] 			= \Input::post('payment_status');

            switch ($arrPayment['status']) {
                case 'Completed':
                    $objOrder->date_paid = time();
                    $objOrder->updateOrderStatus($this->new_order_status);
                    break;

                case 'Canceled_Reversal':
                case 'Denied':
                case 'Expired':
                case 'Failed':
                case 'Voided':
                    break;

                case 'In-Progress':
                case 'Partially_Refunded':
                case 'Pending':
                case 'Processed':
                case 'Refunded':
                case 'Reversed':
                    break;
            }

            $objOrder->payment_data = $arrPayment;
            $objOrder->save();

            \System::log('PayPal Payments Pro: doDirectPayment API Response() Product Collection ID: '.$objOrder->id.', ACK: '.$_SESSION['nvpResArray']['ACK'].', TRANSACTIONID: '.$_SESSION['nvpResArray']['TRANSACTIONID'].'', __METHOD__, TL_GENERAL);
            return true;
            
        } else {
            \System::log('PayPal Payments Pro: doDirectPayment API Response() Product Collection ID: '.$objOrder->id.', L_SHORTMESSAGE0: '.$_SESSION['nvpResArray']['SHORTMESSAGE0'].', TRANSACTIONID: '.$_SESSION['nvpResArray']['TRANSACTIONID'].'', __METHOD__, TL_ERROR);
             return false;
        }	
				
  	}
							
							


	
    /**
     * Process PayPal Instant Payment Notifications (IPN)
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
      	return true;
    }
    
    


    /**
     * Return the PayPal form.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
    		
    		$this->debuggit = false;
    		
    		if (!\Input::post('PaymentAction') != NULL) { 
    			return $this->ccForm();
	 			}
	 			
	 			
				// get form fields
					
					// cc
					$creditCardType = \Input::post('CreditCardType');
					$firstName = \Input::post('BillingFirstName');
					$creditCardNumber = \Input::post('CreditCardNumber');
					$issueNumber = \Input::post('IssueNumber');
					$padDateMonth = \Input::post('ExpMonth');
					$expDateYear = \Input::post('ExpYear');
					$cvv2Number = \Input::post('CVV2');
						
					// billing address
					if ($this->use_cc_billing == 1){
						$address1 = \Input::post('BillingStreet1');
						$address2 = \Input::post('BillingStreet2');
						$city = \Input::post('BillingCityName');
						$state = \Input::post('BillingStateOrProvince');
						$zip = \Input::post('BillingPostalCode');
						$countryCode = \Input::post('BillingCountry');
					}
										
					$currencyCode = $this->paypal_currency_code;

					if ($creditCardNumber == NULL || $cvv2Number == NULL || $firstName == NULL ) { 
							return $this->ccForm("Please complete credit card fields");
					}
					
					
					if ($creditCardType == "Maestro" && $issueNumber == NULL) { 
							return $this->ccForm("Please enter an issue number");
					}
					

					if ($this->use_cc_billing == 1){
				
							if ($address1 == NULL ||
							 //	$address2 == NULL ||
							 	$city == NULL ||
							 	$state == NULL ||
							 	$zip == NULL ||
							 	$countryCode == NULL) { 
							 		return $this->ccForm("Please complete billing address fields"); 
							 		}
					 	}
					 		

				
			  $arrData     = array();
			  $surchargeData = array();
        $fltDiscount = 0;
        $i           = -1;
				$z           = -1;
				

        foreach ($objOrder->getItems() as $objItem) {
            // Set the active product for insert tags replacement
            if ($objItem->hasProduct()) {
                Product::setActive($objItem->getProduct());
            }

            $strOptions = '';
            $arrOptions = Isotope::formatOptions($objItem->getOptions());

            Product::unsetActive();

            if (!empty($arrOptions)) {

                array_walk(
                    $arrOptions,
                    function(&$option) {
                        $option = $option['label'] . ': ' . $option['value'];
                    }
                );

                $strOptions = ' (' . implode(', ', $arrOptions) . ')';
            }

            $arrData[++$i]['item_number'] 	= $objItem->getSku();
            $arrData[$i]['item_name']     	= $objItem->getName() . $strOptions;
            $arrData[$i]['amount']        	= $objItem->getPrice();
            $arrData[$i]['quantity']      	= $objItem->quantity;
  				//	$arrData[$i]['desc']      			= trim(preg_replace('/\s\s+/', ' ',strip_tags(str_replace('[nbsp]','',$objItem->getProduct()->description))));
        }

        foreach ($objOrder->getSurcharges() as $objSurcharge) {

            if (!$objSurcharge->addToTotal) {
                continue;
            }

            // PayPal does only support one single discount item
            if ($objSurcharge->total_price < 0) {
                $fltDiscount -= $objSurcharge->total_price;
                continue;
            }

            $surchargeData[++$z]['surcharge_name'] = $objSurcharge->label;
            $surchargeData[$z]['surcharge_amount'] = $objSurcharge->total_price;
        }
        
        
			        if ($this->debuggit) {
				 				echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$arrData: </strong>';
				        print_r($arrData);
				        echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$surchargeData: </strong>';
				 				print_r($surchargeData);
				       
			      	}
	

		$methodToCall = 'doDirectPayment';
		
		if ($address2 != NULL) {  $address2 = ", ".$address2; }
		
		// PayPal records this IP addresses as a means to detect possible fraud. (Required) 
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		
		
		if ($this->use_cc_billing == 1){ 
			
			 $nvpstr=
			  '&PAYMENTACTION='.$this->paypal_payment_type
			 .'&IPADDRESS='.$ipAddress
			 .'&AMT='.number_format((float)$objOrder->total, 2, '.', '')
		 	 .'&ITEMAMT='.number_format((float)$objOrder->total, 2, '.', '')
			 .'&CREDITCARDTYPE='.$creditCardType
			 .'&ACCT='.$creditCardNumber
			 .'&EXPDATE='.$padDateMonth.$expDateYear
			 .'&CVV2='.$cvv2Number
			 .'&FIRSTNAME='. urlencode($firstName)
			 .'&LASTNAME=&STREET='. urlencode($address1.$address2)
			 .'&CITY='.urlencode($city)
			 .'&STATE='.urlencode($state)
			 .'&ZIP='.urlencode($zip)
			 .'&COUNTRYCODE='.$countryCode
			 .'&CURRENCYCODE='.$currencyCode;
			 
		} else {
			
			$objAddress = $objOrder->getBillingAddress();
			
		 	$nvpstr=
		 	 '&PAYMENTACTION='.$this->paypal_payment_type
		 	.'&IPADDRESS='.$ipAddress
			.'&AMT='.number_format((float)$objOrder->total, 2, '.', '')
		 	.'&ITEMAMT='.number_format((float)$objOrder->total, 2, '.', '')
		 	.'&CREDITCARDTYPE='.$creditCardType
		 	.'&ACCT='.$creditCardNumber
			.'&EXPDATE='.$padDateMonth.$expDateYear
		 	.'&CVV2='.$cvv2Number
		 	.'&FIRSTNAME='.urlencode($firstName)
		 	.'&LASTNAME=&STREET='.urlencode($objAddress->street_1.", ".$objAddress->street_2)
		 	.'&CITY='.urlencode($objAddress->city)
		 	.'&STATE='.urlencode($state)
		 	.'&ZIP='.urlencode($objAddress->postal)
		 	.'&COUNTRYCODE='.$objAddress->country
		 	.'&CURRENCYCODE='.$currencyCode;
		 	
		}
		

	
		if ($creditCardType == "Maestro") { 
			$nvpstr .= '&ISSUENUMBER='.$issueNumber;
		}
		
			$nvpstr .= '&RETURNFMFDETAILS=1';

		

					// items
					for ($i=0; $i < count($arrData); $i++) {
					
						$nvpstr .= '&L_NAME'.$i.'='. urlencode($arrData[$i]['item_name']);
						$nvpstr .= '&L_NUMBER'.$i.'='.$arrData[$i]['item_number'];
						$nvpstr .= '&L_AMT'.$i.'='.number_format((float)$arrData[$i]['amount'], 2, '.', '');  
						$nvpstr .= '&L_QTY'.$i.'='.$arrData[$i]['quantity'];
					}
					
					// surcharges
					$z=0;
					for ($i=count($arrData); $i < count($surchargeData)+(count($arrData)); $i++) {
						$nvpstr .= '&L_NAME'.$i.'='. urlencode($surchargeData[$z]['surcharge_name']);
						$nvpstr .= '&L_AMT'.$i.'='.number_format((float)$surchargeData[$z]['surcharge_amount'], 2, '.', '');
					}
		
		

         return $this->hash_call($methodToCall, $nvpstr, $objModule, $objOrder); 

    
    }
    
    

	   protected function ccForm($error = "") {	
	   	
					$objTemplate = new \FrontendTemplate('iso_payment_paypalpaymentspro');
					$objTemplate->action = \Environment::get('base') . \Environment::get('request');
					
				 $_SESSION['CCBILLING'] = $this->use_cc_billing;

				$objTemplate->submitError = $error;
				$objTemplate->paypal_allowedcc_types = unserialize($this->paypal_allowedcc_types);
			
				
				if (\Input::post('CreditCardType') != NULL) { $objTemplate->CreditCardType = (\Input::post('CreditCardType')); }
				if (\Input::post('CreditCardNumber') != NULL) { $objTemplate->CreditCardNumber = (\Input::post('CreditCardNumber')); }
				if (\Input::post('IssueNumber') != NULL) { $objTemplate->IssueNumber = (\Input::post('IssueNumber')); }
				if (\Input::post('CVV2') != NULL) { $objTemplate-> CVV2 = (\Input::post('CVV2')); }
				if (\Input::post('BillingFirstName') != NULL) { $objTemplate->BillingFirstName = (\Input::post('BillingFirstName')); }
				if (\Input::post('BillingStreet1') != NULL) { $objTemplate->BillingStreet1 = (\Input::post('BillingStreet1')); }
				if (\Input::post('BillingStreet2') != NULL) { $objTemplate->BillingStreet2 = (\Input::post('BillingStreet2')); }		
				if (\Input::post('BillingCityName') != NULL) { $objTemplate->BillingCityName = (\Input::post('BillingCityName')); }
				if (\Input::post('BillingStateOrProvince') != NULL) { $objTemplate->BillingStateOrProvince = (\Input::post('BillingStateOrProvince')); }
				if (\Input::post('BillingPostalCode') != NULL) { $objTemplate->BillingPostalCode = (\Input::post('BillingPostalCode')); }	
					
					return $objTemplate->parse();
		}

    

		
				function hash_call($methodName,$nvpStr,$objModule,$objOrder) {
					
				$this->endpoint = '/nvp';
				if ($this->paypal_environment != 'sandbox') {
					$this->host = "https://api-3t.paypal.com";
				} else {
					//sandbox
							$this->host = "https://api-3t.sandbox.paypal.com";
				}
					
					
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $this->host.$this->endpoint);
						curl_setopt($ch, CURLOPT_VERBOSE, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($ch, CURLOPT_POST, 1);
						
						$this->version = '98.0'; // https://developer.paypal.com/docs/classic/release-notes/#MerchantAPI

		echo $this->host.$this->endpoint;
		
						$nvpreq = "METHOD=".urlencode($methodName)."&version=".urlencode($this->version)."&PWD=".urlencode($this->paypal_api_password)."&USER=".urlencode($this->paypal_api_username)."&SIGNATURE=".urlencode($this->paypal_api_signature).$nvpStr;
	
	
 if ($this->debuggit) {	
		echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$nvpreq: </strong>';
				        print_r($nvpreq);	
 }



 
						curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
						$httpResponse = curl_exec($ch);
						
						

						
 if ($this->debuggit) {	
		echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$httpResponse: </strong>';
				        print_r($httpResponse);	
 }
			
						
				
						$nvpResArray=$this->deformatNVP($httpResponse);
						
					//	$nvpReqArray=$this->deformatNVP($nvpreq);
	
						
			
						
						
						if (curl_errno($ch))
						{
							die("CURL send a error during perform operation: ".curl_errno($ch));
						} 
						else 
						{
							curl_close($ch);
						}
						
						
	
		
 	if ($this->debuggit) {	
		echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$nvpResArray: </strong>';
				        print_r($nvpResArray);	
 	}

	$_SESSION['nvpResArray'] = $nvpResArray;
	$nvpReqArray=$this->deformatNVP($nvpreq);
	
	
	
 	if ($this->debuggit) {	
		echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$nvpReqArray: </strong>';
				        print_r($nvpReqArray);	
 	}



						if ($nvpResArray['ACK'] == "Success"){
							 header('Location: '.\Environment::get('base') . $objModule->generateUrlForStep('complete',$objOrder));	
							die();
						}else {
						 header('Location: '.\Environment::get('base') . $objModule->generateUrlForStep('failed'));	
							die();
						}
		
	
				}
		

    
    
    
	
				function deformatNVP($nvpstr) {
			
					$intial=0;
					$nvpArray = array();
					while(strlen($nvpstr))
					{
						$keypos= strpos($nvpstr,'='); 
						$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr); 
						$keyval=substr($nvpstr,$intial,$keypos);
						$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
						$nvpArray[urldecode($keyval)] =urldecode( $valval);
						$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
					 }
					return $nvpArray;
				}
	
    
}
