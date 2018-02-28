<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Splitit\Paymentmethod\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class ExpressConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressConfigProvider implements ConfigProviderInterface
{
    const IN_CONTEXT_BUTTON_ID = 'splitit-express-in-context-button';

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var string[]
     */
    protected $methodCodes = [
        PaymentRedirect::CODE
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->localeResolver = $localeResolver;
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'splititExpress' => [
                    'paymentAcceptanceMarkHref' => "ashwani",
                    'paymentAcceptanceMarkSrc' => "ashwani",
                    'isContextCheckout' => false,
                    'inContextConfig' => []
                ]
            ]
        ];

//        $isInContext = $this->isInContextCheckout();
//        if ($isInContext) {
//            $config['payment']['splititExpress']['isContextCheckout'] = $isInContext;
//            $config['payment']['splititExpress']['inContextConfig'] = [
//                'inContextId' => self::IN_CONTEXT_BUTTON_ID,
//                'merchantId' => 123456789,
//                'path' => $this->urlBuilder->getUrl('splitit/express/gettoken', ['_secure' => true]),
//                'clientConfig' => [
//                    'environment' => 'sandbox',
//                    'locale' => $locale,
//                    'button' => [
//                        self::IN_CONTEXT_BUTTON_ID
//                    ]
//                ],
//            ];
//        }

//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $api = $objectManager->get('Splitit\Paymentmethod\Model\Api');
//        $response=$api->apiLogin();
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
//                $config['payment']['splititExpress']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
                $config['payment']['splititExpress']['redirectUrl'][$code] = $this->urlBuilder->getUrl("splititpaymentmethod/payment/redirect");
                $config['payment']['splititExpress']['billingAgreementCode'][$code] = "";
            }
        }
        return $config;
    }
    
    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }
}
