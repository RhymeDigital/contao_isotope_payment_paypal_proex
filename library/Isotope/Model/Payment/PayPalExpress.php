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
 * Class PaypalExpress
 *
 * Handle Paypal Express payments
 * PHP version 5
 * @copyright  360fusion  2014
 * @author     Darrell Martin <darrell@360fusion.co.uk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 
 */
class PayPalExpress extends Payment implements IsotopePayment
{

	private $endpoint;
	private $host;
	private $gate;
	

  /**
     * Process payment on checkout page.
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  mixed
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {

				$this->debuggit = false;
        $this->endpoint = '/nvp';
				if ($this->paypal_environment != 'sandbox') {
				$this->host = "api-3t.paypal.com";
				$this->gate = 'https://www.paypal.com/cgi-bin/webscr?';
				} else {
					//sandbox
					$this->host = "api-3t.sandbox.paypal.com";
					$this->gate = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
					}
       
  			$token = $_GET['token'];
	   		$PayerID = $_GET['PayerID'];
	   
			
							if ($this->debuggit) {
								echo '-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$this->new_order_status: </strong>';
								print_r($this->new_order_status);
							}        
	        

		if ($token) {
						
			 $checkoutDetails = $this->getCheckoutDetails($token);	
			 $paymentDetails = $this->doPayment($checkoutDetails);
			
						if ($this->debuggit) {		
							echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$checkoutDetails: </strong>';
							print_r($checkoutDetails);
							
							echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$paymentDetails: </strong>';
										print_r($paymentDetails);		
						}

			
			if ($paymentDetails['PAYMENTINFO_0_ACK'] == "Success" && !$paymentDetails['L_SEVERITYCODE0']) {
		
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

            \System::log('PayPal Express: GetExpressCheckoutDetails API Response() Product Collection ID: '.$objOrder->id.', ACK: '.$paymentDetails['ACK'].', TRANSACTIONID: '.$paymentDetails['PAYMENTINFO_0_TRANSACTIONID'].'', __METHOD__, TL_GENERAL);
            return true;
            
        } else {
            \System::log('PayPal Express: GetExpressCheckoutDetails API Response() Product Collection ID: '.$objOrder->id.', L_SHORTMESSAGE0: '.$paymentDetails['L_SHORTMESSAGE0'].', TRANSACTIONID: '.$paymentDetails['PAYMENTINFO_0_TRANSACTIONID'].'', __METHOD__, TL_ERROR);
             return false;
        }	
			
		} else {
		   	 return;
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
    	
    	
		    $this->endpoint = '/nvp';
				if ($this->paypal_environment != 'sandbox') {
				$this->host = "api-3t.paypal.com";
				$this->gate = 'https://www.paypal.com/cgi-bin/webscr?';
				} else {
					//sandbox
					$this->host = "api-3t.sandbox.paypal.com";
					$this->gate = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
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
				        echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$data: </strong>';
			      	}

					$data = array();
			 		
					// items
					for ($i=0; $i < count($arrData); $i++) {
						$Item = array(
							'L_PAYMENTREQUEST_0_NAME'.$i 					=> $arrData[$i]['item_name'],
							'L_PAYMENTREQUEST_0_NUMBER'.$i  			=> $arrData[$i]['item_number'],
				//	'L_PAYMENTREQUEST_0_DESC'.$i  				=> $arrData[$i]['desc'],
							'L_PAYMENTREQUEST_0_AMT'.$i  					=> $arrData[$i]['amount'],
							'L_PAYMENTREQUEST_0_QTY'.$i 					=> $arrData[$i]['quantity'],
						); $data += $Item;
					}
					// surcharges
					$z=0;
					for ($i=count($arrData); $i < count($surchargeData)+(count($arrData)); $i++) {
						$Item = array(
							'L_PAYMENTREQUEST_0_NAME'.$i 					=> $surchargeData[$z]['surcharge_name'],
							'L_PAYMENTREQUEST_0_AMT'.$i  					=> $surchargeData[$z]['surcharge_amount'],
						); $data += $Item; $z++;
					}
					
	
					$Item = array(
					'METHOD'													=> SetExpressCheckout,
					'RETURNURL'											 	=> \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder),
					'CANCELURL' 											=> \Environment::get('base') . $objModule->generateUrlForStep('failed'),
					'PAYMENTREQUEST_0_CURRENCYCODE'		=> $this->paypal_currency_code,
					'PAYMENTREQUEST_0_PAYMENTACTION' 	=> $this->paypal_payment_type,
					'PAYMENTREQUEST_0_AMT'						=> $objOrder->total,
					'PAYMENTREQUEST_0_ITEMAMT' 				=> $objOrder->total, // $objOrder->subtotal,
					'PAYMENTREQUEST_0_SHIPDISCAMT'		=> $fltDiscount,
					'LOGOIMG'													=> '',
					'CARTBORDERCOLOR'									=> FFFFFF,
					'ALLOWNOTE'												=> 1,
					'LOCALECODE'											=> GB
					);$data += $Item;
					
					/*	
						'PAYMENTREQUEST_0_TAXAMT' 				=> '',
						'PAYMENTREQUEST_0_SHIPPINGAMT'		=> $objOrder->total - $objOrder->tax_free_total,
						'PAYMENTREQUEST_0_HANDLINGAMT'		=> '',
					*/
					
				
 			return $this->doExpressCheckout($data);
        
    }
    
    
    
		public function doExpressCheckout($data) {
			
			 		if ($this->debuggit) { print_r($data); }

			
					$query = $this->buildQuery($data);
					$result = $this->response($query);

					if (!$result) return false;
					$response = $result->getContent();
					

					$return = $this->responseParse($response);
			

					if ($this->debuggit) {	
						echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$response: </strong>';
						print_r($response);
					}				
					
					
					if ($return['ACK'] == 'Failure') {
						
								if ($this->debuggit) {
									echo '<br>-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><strong>$return: </strong><br>';
								}
						echo 'TIMESTAMP: '.$return['TIMESTAMP'].'<br>';	
						echo 'CORRELATIONID: '.$return['CORRELATIONID'].'<br>';	
						echo 'ACK: '.$return['ACK'].'<br>';	
						echo 'VERSION: '.$return['VERSION'].'<br>';	
						echo 'BUILD: '.$return['BUILD'].'<br>';	
						echo 'L_ERRORCODE0: '.$return['L_ERRORCODE0'].'<br>';	
						echo 'L_SEVERITYCODE0: '.$return['L_SEVERITYCODE0'].'<br>';	
						echo 'L_SHORTMESSAGE0: '.$return['L_SHORTMESSAGE0'].'<br>';	
						echo 'L_LONGMESSAGE0: '.$return['L_LONGMESSAGE0'].'<br>';
						exit;
					}

					if ($return['ACK'] == 'Success') {
						header('Location: '.$this->gate.'cmd=_express-checkout&useraction=commit&token='.$return['TOKEN'].'');
						die();
					}
			
		}
    

    
    
		private function response($data) {
			$r = new \HTTPRequest($this->host, $this->endpoint, 'POST', true);
			$result = $r->connect($data);
			if ($result<400) return $r;
			return false;
		}
	
		private function buildQuery($data = array()) {
		
		  $data['USER'] = $this->paypal_api_username;
			$data['PWD'] = $this->paypal_api_password;
			$data['SIGNATURE'] = $this->paypal_api_signature;
			$data['VERSION'] = '98.0';
			$query = http_build_query($data);
			return $query;
		}
		
		private function responseParse($resp){
			$a=explode("&", $resp);
			$out = array();
			foreach ($a as $v){
				$k = strpos($v, '=');
				if ($k) {
					$key = trim(substr($v,0,$k));
					$value = trim(substr($v,$k+1));
					if (!$key) continue;
					$out[$key] = urldecode($value);
				} else {
					$out[] = $v;
				}
			}
			return $out;
		}
		
		
		
		public function getCheckoutDetails($token){
					$data = array(
					'TOKEN' => $token,
					'METHOD' =>'GetExpressCheckoutDetails');
					$query = $this->buildQuery($data);
					$result = $this->response($query);
					if (!$result) return false;
					$response = $result->getContent();
					$return = $this->responseParse($response);
					return($return);
		}
		
		
				
		public function doPayment($checkoutDetails){
					$token = $_GET['token'];
					$payer = $_GET['PayerID'];
					$details = $this->getCheckoutDetails($token);
					if (!$details) return false;
					
					$data = array(
						'TOKEN' 														=> $token,
						'PAYERID' 													=> $payer,
						'PAYMENTREQUEST_0_PAYMENTACTION' 		=> $this->paypal_payment_type,
						'PAYMENTREQUEST_0_CURRENCYCODE'  		=> $checkoutDetails['CURRENCYCODE'],
						'PAYMENTREQUEST_0_AMT'							=> $checkoutDetails['AMT'],
						'PAYMENTREQUEST_0_ITEMAMT'					=> $checkoutDetails['ITEMAMT'],
						'METHOD' 														=>'DoExpressCheckoutPayment'
					);
					
					
					for ($i=0; $i <= 100; $i++) {
						if ($checkoutDetails['L_PAYMENTREQUEST_0_NAME'.$i]){
							
							$Item = array(
								'L_PAYMENTREQUEST_0_NAME'.$i					=> $checkoutDetails['L_PAYMENTREQUEST_0_NAME'.$i],
								'L_PAYMENTREQUEST_0_NUMBER'.$i				=> $checkoutDetails['L_PAYMENTREQUEST_0_NUMBER'.$i],
								'L_PAYMENTREQUEST_0_QTY'.$i						=> $checkoutDetails['L_PAYMENTREQUEST_0_QTY'.$i],
								'L_PAYMENTREQUEST_0_AMT'.$i						=> $checkoutDetails['L_PAYMENTREQUEST_0_AMT'.$i],
							);$data += $Item;
						}
					}
					
					$query = $this->buildQuery($data);
					$result = $this->response($query);
					if (!$result) return false;
					$response = $result->getContent();
					$return = $this->responseParse($response);
					return($return);
		}
				
				
		public function getTransactionDetails($trans_id){
					$data = array(
					'TRANSACTIONID' => $trans_id,
					'METHOD' =>'GetTransactionDetails');
					$query = $this->buildQuery($data);
					$result = $this->response($query);
					if (!$result) return false;
					$response = $result->getContent();
					$return = $this->responseParse($response);
					return($return);
		}
    
}
