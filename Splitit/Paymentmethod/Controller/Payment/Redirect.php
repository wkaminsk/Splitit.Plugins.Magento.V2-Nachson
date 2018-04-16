<?php

namespace Splitit\Paymentmethod\Controller\Payment;

class Redirect extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Splitit\Paymentmethod\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface $order 
     */
    protected $order;
    protected $quoteFactory;
    protected $paymentForm;
    protected $api;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, 
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, 
    \Magento\Sales\Api\Data\OrderInterface $order, 
    \Magento\Quote\Model\QuoteFactory $quoteFactory, 
    \Splitit\Paymentmethod\Helper\Data $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData = $helperData;
        $this->order = $order;
        $this->quoteFactory = $quoteFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        $this->paymentForm = $objectManager->get('\Splitit\Paymentmethod\Model\PaymentForm');
        $this->api = $objectManager->get('\Splitit\Paymentmethod\Model\Api');
        parent::__construct($context);
    }

    public function execute() {
//        die("redirect controller");
        $data = $this->paymentForm->orderPlaceRedirectUrl();
        $order = $this->checkoutSession->getLastRealOrder();
        $orderId = $order->getEntityId();
        $payment=$order->getPayment();
//        var_dump($this->checkoutSession->getData());exit;
        $payment->setTransactionId($this->checkoutSession->getSplititInstallmentPlanNumber());
        $payment->save();
        $order->save();
        $curlRes = $this->paymentForm->updateRefOrderNumber($this->api, $order);
//        echo $orderId;
//        print_r($curlRes);exit;
        if (isset($curlRes["status"]) && $curlRes["status"]) {
            $this->_redirect($data['checkoutUrl']);
        }
    }

}
