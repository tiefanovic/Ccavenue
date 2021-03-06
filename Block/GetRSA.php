<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 01:55 م
 */

namespace AWstreams\Ccavenue\Block;

use Magento\Customer\Model\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use AWstreams\Ccavenue\Helper\Data;

class GetRSA extends \Magento\Framework\View\Element\Template
{

    protected $_checkoutSession;
    protected $_orderConfig;
    protected $httpContext;
    protected $_ccavenueModel;
    protected $_logger;
    protected $_helper;
    protected $_template = 'mobile_redirect.phtml';
    protected $_request;
    protected $orderRepository;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \AWstreams\Ccavenue\Model\Ccavenue $ccavenueModel,
         \Magento\Framework\App\RequestInterface $request,
         \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->_helper = $paymentHelper;
        $this->_ccavenueModel = $ccavenueModel;
        $this->_logger = $logger;
        $this->_request = $request;
        $this->orderRepository = $orderRepository;
    }
    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }
    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order_is_ok = true;
        $order_error_message = '';
        $params = $this->_request->getParams();
        try{
            $order = $this->orderRepository->get($params['order_id']);
            if( $order->getState() != Order::STATE_NEW )
                $order_error_message = __( 'Order was already processed or session information expired.' );
            elseif( !($additional_info = $order->getPayment()->getAdditionalInformation()) || !is_array( $additional_info ) )
                $order_error_message = __( 'Couldn\'t extract payment information from order.' );
            if( !empty( $order_error_message ) )
                $order_is_ok = false;
            
            $form_data  = '';
            $form_url   = '';
            if( $order_is_ok )
            {
                //$this->_template = 'mobile-merchant-page.phtml';
                $arrPaymentPageData = $this->_ccavenueModel->getMerchantPageData($order, true);
                $form_data = $arrPaymentPageData['params'];
                $form_url = $arrPaymentPageData['url'];
                $paymentMethod= $order->getPayment()->getMethod();
                $order->addStatusHistoryComment( 'CcAvenue :: redirecting to payment page with Method: '.$paymentMethod );
                $order->save();
                $this->addData(
                    [
                        'order_ok' => $order_is_ok,
                        'error_message' => $order_error_message,
                        'order_id'  => $order->getIncrementId(),
                        'form_data'  => $form_data,
                        'form_url'  => $form_url,
                    ]
                );
            }
        }catch(\Exception $e){
            $this->addData(
                    [
                        'order_ok' => false,
                        'error_message' => $e->getMessage()
                    ]
                );
        }
    }
    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible(Order $order)
    {
        return !in_array(
            $order->getStatus(),
            $this->_orderConfig->getInvisibleOnFrontStatuses()
        );
    }
    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder(Order $order)
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH)
        && $this->isVisible($order);
    }
}