<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 03:24 م
 */

namespace AWstreams\Ccavenue\Controller\Payment;


use Magento\Framework\App\ResponseInterface;
use AWstreams\Ccavenue\Controller\Checkout;


class MobilePageResponse extends Checkout
{

    public function execute()
    {
        /*
         *order_id=8&tracking_id=108007928348&bank_ref_no=593578&
         * order_status=Success&failure_message=&payment_mode=Credit Card&
         * card_name=MasterCard&status_code=00&status_message=Approved
         * cy=AED&amount=1805.0&billing_name=Ahmed Atef&billing_address=sdfgasdf&
         * billing_city=AL QURAIYA AREAFUJ&billing_state=Fujairah&billing_zip=000000&
         * billing_country=United Arab Emirates&billing_tel=0504714595&billing_email=wesrtyu@gmail.com&
         * delivery_name=Ahmed Atef&delivery_address=sdfgasdf&delivery_city=AL QURAIYA AREAFUJ&
         * delivery_state=Fujairah&delivery_zip=000000&delivery_country=United Arab Emirates&
         * delivery_tel=0504714595&merchant_param1=8&merchant_param2=0&merchant_param3=SECURE&
         * merchant_param4=20190221144006&merchant_param5=&vault=Y&offer_type=null&offer_code=null&
         * discount_value=0.0&mer_amount=1805.0&eci_value=05&card_holder_name=bank_qsi_no=2090020801&
         * bank_receipt_no=905301593578&customer_card_id=201905220134457&merchant_param6=5123452346
         */
        $params = $this->getRequest()->getParams();
        $returnUrl = $this->_url->getUrl('ccavenue/payment/response/status/yes');
        try{
            if($params && !empty($params['encResp'])){
                $params = $this->_paymentModel->decryptData($params['encResp']);
                parse_str( $params, $params);
                $orderId = $params['order_id'];
                $order = $this->orderRepository->get($orderId);
                $success = true;
                if($params['order_status'] != 'Success') $success = false;
                if($success) {
                    $this->getHelper()->processOrder($order);
                     $returnUrl = $this->_url->getUrl('ccavenue/payment/response/status/yes');
                }else {
                    $this->getHelper()->orderFailed($order);
                    $message = __('Error: Payment Failed, ' . $params['failure_message']);
                    $this->messageManager->addError( $message );
                    $returnUrl = $this->_url->getUrl('ccavenue/payment/response/status/no');
                }
            }else{
                 $returnUrl = $this->_url->getUrl('ccavenue/payment/response/status/canceled');
            }
        }catch(\Exception $e){
            //$this->messageManager->addError( $e->getMessage() );
            $returnUrl = $this->_url->getUrl('ccavenue/payment/response/status/no');
        }
        $this->orderRedirect($returnUrl);
    }
}