<?php

namespace AWstreams\Ccavenue\Helper;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{


    /**
     * Path to store config if extension is enabled
     *
     * @var string
     */
    const AWS_CCAVENUE_ACTIVE = 'payment/ccavenue/active';
    protected $customerSession;
    protected $checkoutSession;
    protected $orderCommentSender;
    protected $orderManagement;
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    )
    {
        parent::__construct($context);
        $this->customerSession = $session;
        $this->checkoutSession = $checkoutSession;
        $this->orderCommentSender = $orderCommentSender;
        $this->orderManagement = $orderManagement;
    }
    /**
     * Check if extension enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::AWS_CCAVENUE_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function restoreQuote()
    {
        return $this->checkoutSession->restoreQuote();
    }
    public function cancelOrder($order, $comment)
    {
        $gotoSection = false;
        if(!empty($comment)) {
            $comment = 'CcAvenue :: ' . $comment;
        }
        if ($order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            $gotoSection = true;
        }
        return $gotoSection;
    }
    public function orderFailed($order) {
        if ($order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory( \Magento\Sales\Model\Order::STATE_CANCELED , 'CCAVENUE :: payment has failed.', $customerNotified );
            $order->save();
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function sendOrderEmail($order) {
        $result = true;
        try{
            if($order->getState() != $order::STATE_PROCESSING) {
                $this->orderCommentSender->send($order, true, '');
            }
            else{
                $this->orderManagement->notify($order->getEntityId());
            }
        } catch (\Exception $e) {
            $result = false;
            $this->_logger->critical($e);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function processOrder($order) {

        if ($order->getState() != $order::STATE_PROCESSING) {
            $order->setStatus($order::STATE_PROCESSING);
            $order->setState($order::STATE_PROCESSING);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory( $order::STATE_PROCESSING , 'CCAVENUE :: Order has been paid.', $customerNotified );
            $order->save();
            return true;
        }
        return false;
    }
}