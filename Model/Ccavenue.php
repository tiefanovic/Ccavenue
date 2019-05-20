<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AWstreams\Ccavenue\Model;

/**
 * Cash on delivery payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Ccavenue extends \Magento\Payment\Model\Method\AbstractMethod
{
    const GATEWAY_URL   =   'https://secure.ccavenue.ae/transaction/transaction.do';
	const PAYMENT_METHOD_CCAVENUE_CODE = 'ccavenue';
	const AWS_CCAVENUE_TITLE = 'payment/ccavenue/title';
	const AWS_CCAVENUE_ACCESS_CODE = 'payment/ccavenue/access_code';
	const AWS_CCAVENUE_MERCHANT_ID = 'payment/ccavenue/merchant_id';
	const AWS_CCAVENUE_ENCRYPTION_KEY = 'payment/ccavenue/encryption_key';
	const AWS_CCAVENUE_INSTRUCTION = 'payment/ccavenue/instructions';

    protected $_code = self::PAYMENT_METHOD_CCAVENUE_CODE;
    protected $_storeScope =  \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

  	public function getTitle() 
	{

		return $this->_scopeConfig->getValue(self::AWS_CCAVENUE_TITLE, $this->_storeScope);
	}

	public function getAccessCode() 
	{

		return $this->_scopeConfig->getValue(self::AWS_CCAVENUE_ACCESS_CODE, $this->_storeScope);
	}

	public function getMerchantId()
	{
	 	return $this->_scopeConfig->getValue(self::AWS_CCAVENUE_MERCHANT_ID, $this->_storeScope);
	}

	public function getEncryptionKey()
	{
 		return $this->_scopeConfig->getValue(self::AWS_CCAVENUE_ENCRYPTION_KEY, $this->_storeScope);
	}


	public function getRequestUrl(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$url = $objectManager->get('\Magento\Framework\UrlInterface');
		return $url->getUrl('ccavenue/payment/redirect');
	}

	public function getResponseUrl($ismobile = false){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$url = $objectManager->get('\Magento\Framework\UrlInterface');
		return $ismobile ? $url->getUrl('ccavenue/payment/mobilepageresponse') : $url->getUrl('ccavenue/payment/merchantpageresponse');
	}

    public function getInstruction(){
        return $this->_scopeConfig->getValue(self::AWS_CCAVENUE_INSTRUCTION, $this->_storeScope);
    }
	public function getCountryName($countryId){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$countryModel = $objectManager->get('\Magento\Directory\Model\Country'); 
		$countryModel->loadByCode($countryId);

     	return $countryModel->getName();
	}

	public function getQuote(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 

		if($cart->getQuote()->getIsActive())
			return $cart->getQuote();
		else
			return NULL;
	}
    public function getMerchantPageData(\Magento\Sales\Model\Order $order, $ismobile = false){
        $queryData = [
                'params'=>[
                    'command' => 'initiateTransaction',
                    'encRequest' => $this->getEncryptedData($order, $ismobile),
                    'access_code' => $this->getAccessCode()
                ],
                'url'   =>  self::GATEWAY_URL
            ];
        return $queryData;
    }
	public function getEncryptedData($order, $ismobile = false){
        //Configuration
        $access_code        = trim($this->getAccessCode());
        $encryption_key     = trim($this->getEncryptionKey());
        $ccavenue_title     = trim($this->getTitle());

		//General
        $merchant_id        = trim($this->getMerchantId());
        $order_id           = (int) $order->getIncrementId();
        $currency           = trim($order->getOrderCurrencyCode());
        $payment_total      = (float) $order->getGrandTotal();
        //$payment_total      = (float) 0.1;
        $language           = 'EN';

        //URL
        $Redirect_Url       = $this->getResponseUrl($ismobile);
        $Cancel_Url         = $this->getResponseUrl($ismobile);

        //Billing Address
        $billing_address    = $order->getBillingAddress()->getData();
        $billing_name       = $billing_address['firstname'] ." ". $billing_address['lastname'];
        $bill_address       = $billing_address['street'];
        $billing_city       = $billing_address['city'];
        $billing_zip        = $billing_address['postcode'];
        $billing_tel        = $billing_address['telephone'];
        $billing_email      = $billing_address['email'];
        $billing_state      = $billing_address['region'];
        $billing_country    = $this->getCountryName($billing_address['country_id']);

		//Delivery Address
        $delivery_address   = $order->getShippingAddress()->getData();
        $delivery_name      = $delivery_address['firstname']." " . $delivery_address['lastname'];
        $deli_address       = $delivery_address['street'];
        $delivery_city      = $delivery_address['city'];
        $delivery_zip       = $delivery_address['postcode'];
        $delivery_tel       = $delivery_address['telephone'];
        $delivery_state     = $delivery_address['region'];    
        $delivery_country   = $this->getCountryName($delivery_address['country_id']);
        
        //Merchant Params
        $merchant_param1    = (int) $order->getRealOrderId();
        $merchant_param2    = (int) $order->getCustomerId();
        $merchant_param3    = "SECURE";
        $merchant_param4    = date('YmdHis');
        $merchant_param5    = "";
 
        //Merchant Data
        $merchant_data = array();
        $merchant_data['merchant_id']      = $merchant_id;
        $merchant_data['order_id']         = $order_id;
        $merchant_data['currency']         = $currency;
        $merchant_data['amount']           = $payment_total;
        $merchant_data['language']         = $language;

        $merchant_data['redirect_url']     = $Redirect_Url;       
        $merchant_data['cancel_url']       = $Cancel_Url;

        $merchant_data['billing_name']     = $billing_name;
        $merchant_data['billing_address']  = $bill_address;    
        $merchant_data['billing_city']     = $billing_city;
        $merchant_data['billing_state']    = $billing_state;
        $merchant_data['billing_zip']      = $billing_zip; 
        $merchant_data['billing_country']  = $billing_country; 
        $merchant_data['billing_tel']      = $billing_tel;
        $merchant_data['billing_email']    = $billing_email;

        $merchant_data['delivery_name']    = $delivery_name;
        $merchant_data['delivery_address'] = $deli_address;
        $merchant_data['delivery_city']    = $delivery_city;
        $merchant_data['delivery_state']   = $delivery_state;
        $merchant_data['delivery_zip']     = $delivery_zip;
        $merchant_data['delivery_country'] = $delivery_country;
        $merchant_data['delivery_tel']     = $delivery_tel;

        $merchant_data['merchant_param1']  = $merchant_param1;
        $merchant_data['merchant_param2']  = $merchant_param2;
        $merchant_data['merchant_param3']  = $merchant_param3;
        $merchant_data['merchant_param4']  = $merchant_param4;
        $merchant_data['merchant_param5']  = $merchant_param5;
        $merchant_data['customer_identifier'] = $order->getCustomerEmail();
        $merchant_data['integration_type'] = 'iframe_normal';
        $enc_data = '';
        foreach ($merchant_data as $key => $value){
    		$enc_data.=$key.'='.$value.'&';
    	}
    	$enc_data = rtrim($enc_data, '&');
    	//die($enc_data);
        $encrypted_data = $this->encrypt($enc_data,$encryption_key);

        return $encrypted_data;
    }
    public function decryptData($data){
        return $this->decrypt($data, trim($this->getEncryptionKey()));
    }

    /****************** CRYPTO ccavenue *****************/
    public function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	  	$openMode = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
	  	$blockSize = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
	  	if (@mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) 
		{
		      $encryptedText = @mcrypt_generic($openMode, $plainPad);
	      	      @mcrypt_generic_deinit($openMode);
		      			
		} 
		return bin2hex($encryptedText);
	}

	public function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText = $this->hextobin($encryptedText);
	  	$openMode = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		@mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = @mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
	 	@mcrypt_generic_deinit($openMode);
		return $decryptedText;
		
	}
	//*********** Padding Function *********************

	private function pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	private function hextobin($hexString) 
   	 { 
        	$length = strlen($hexString); 
        	$binString="";   
        	$count=0; 
        	while($count<$length) 
        	{       
        	    $subString =substr($hexString,$count,2);           
        	    $packedString = pack("H*",$subString); 
        	    if ($count==0)
		    {
				$binString=$packedString;
		    } 
        	    
		    else 
		    {
				$binString.=$packedString;
		    } 
        	    
		    $count+=2; 
        	} 
  	        return $binString; 
    	  }
}
