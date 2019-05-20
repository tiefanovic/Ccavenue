<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AWstreams\Ccavenue\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use AWstreams\Ccavenue\Model\Ccavenue;

class CcavenueConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Ccavenue::PAYMENT_METHOD_CCAVENUE_CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Ccavenue $ccavenue
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->ccavenue = $ccavenue;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
                'payment' => [
                    'ccavenue' => [],
                ],
            ];
        if($this->method->isAvailable()){
            $config['payment']['ccavenue']['title'] = $this->ccavenue->getTitle();
            $config['payment']['ccavenue']['code'] = $this->methodCode;
            $config['payment']['ccavenue']['isActive'] = $this->ccavenue->isActive();
            $config['payment']['ccavenue']['redirectUrl'] = $this->ccavenue->getRequestUrl();
            $config['payment']['ccavenue']['instructions'] = $this->ccavenue->getInstruction();
        }
        return $config;
    }
}
