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

class MerchantPageCancel extends Checkout
{

    public function execute()
    {
        $order = $this->getOrder();
        $this->getHelper()->orderFailed($order);
        $this->_checkoutSession->restoreQuote();

        $message = __('Error: Payment Failed, Payment page has closed.');
        $this->messageManager->addError( $message );
        $returnUrl = $this->_url->getUrl('checkout/cart');
        /*$orderId = $this->getRequest()->getParam('merchant_reference');
        $order = $this->getOrderById($orderId);
        $returnUrl = $this->getHelper()->getUrl('checkout/onepage/success');

        $responseParams = $this->getRequest()->getParams();
        $paymentResponse = $this->getHelper()->validateResponse($responseParams);
        $paymentModel = $this->getPayfortModel();
        if($paymentResponse == $paymentModel::PAYMENT_STATUS_SUCCESS) {
            $this->getHelper()->processOrder($order);
            $returnUrl = $this->getHelper()->getUrl('checkout/onepage/success');
        }
        elseif($paymentResponse == $paymentModel::PAYMENT_STATUS_CANCELED) {
            $this->_cancelPayment($order, 'User has cancel the payment');
            $this->_checkoutSession->restoreQuote();
            $message = __('You have canceled the payment.');
            $this->messageManager->addError( $message );
            $returnUrl = $this->getHelper()->getUrl('checkout/cart');
        }
        else {
            $this->getHelper()->orderFailed($order);
            $this->_checkoutSession->restoreQuote();

            $message = __('Error: Payment Failed, Please check your payment details and try again.');
            $this->messageManager->addError( $message );
            $returnUrl = $this->getHelper()->getUrl('checkout/cart');
        }*/
        $this->orderRedirect($returnUrl);
    }

}