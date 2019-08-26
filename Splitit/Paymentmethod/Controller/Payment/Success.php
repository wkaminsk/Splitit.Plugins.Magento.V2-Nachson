<?php

namespace Splitit\Paymentmethod\Controller\Payment;

class Success extends \Magento\Framework\App\Action\Action {

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
    protected $logger;
    protected $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Psr\Log\LoggerInterface $logger,
        \Splitit\Paymentmethod\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Splitit\Paymentmethod\Model\PaymentForm $paymentForm,
        \Splitit\Paymentmethod\Model\Api $api,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData = $helperData;
        $this->order = $order;
        $this->quoteFactory = $quoteFactory;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->paymentForm = $paymentForm;
        $this->api = $api;
        $this->orderSender = $orderSender;
        parent::__construct($context);
    }
    
    /**
     * Success the order handle
     * @return void
     **/
    public function execute() {
        $order = $this->checkoutSession->getLastRealOrder();

        $params = $this->getRequest()->getParams();
        if(!$this->checkoutSession->getSplititInstallmentPlanNumber()){
            $this->checkoutSession->setSplititInstallmentPlanNumber($params['InstallmentPlanNumber']);
        }
        $api = $this->paymentForm->_initApi();
        $planDetails = $this->paymentForm->getInstallmentPlanDetails($this->api);

        $this->logger->addDebug('======= get installmentplan details :  ======= ');
        $this->logger->addDebug(print_r($planDetails,TRUE));

        $orderId=$order->getEntityId();
        $orderIncrementId = $order->getIncrementId();
        $orderObj = $this->order->load($orderId);
        $grandTotal = number_format((float) $orderObj->getGrandTotal(), 2, '.', '');
        $planDetails["grandTotal"] = number_format((float) $planDetails["grandTotal"], 2, '.', '');
        $this->logger->addDebug('======= grandTotal(orderObj):' . $grandTotal . ', grandTotal(planDetails):' . $planDetails["grandTotal"] . '   ======= ');
        if ($grandTotal == $planDetails["grandTotal"] && ($planDetails["planStatus"] == "PendingMerchantShipmentNotice" || $planDetails["planStatus"] == "InProgress")) {

            $payment = $orderObj->getPayment();
            $paymentAction = $this->_helperData->getConfig("payment/splitit_paymentredirect/payment_action");

            $payment->setTransactionId($this->checkoutSession->getSplititInstallmentPlanNumber());
            $payment->setParentTransactionId($this->checkoutSession->getSplititInstallmentPlanNumber());
            $payment->setInstallmentsNo($planDetails["numberOfInstallments"]);
            $payment->setIsTransactionClosed(0);
            $payment->setCurrencyCode($planDetails["currencyCode"]);
            $payment->setCcType($planDetails["cardBrand"]["Code"]);
            $payment->setIsTransactionApproved(true);

            $payment->registerAuthorizationNotification($grandTotal);
           /*$payment->setAdditionalInformation(
               [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $planDetails]
           );*/
            $orderObj->addStatusToHistory(
                    $orderObj->getStatus(), 'Payment InstallmentPlan was created with number ID: '
                    . $this->checkoutSession->getSplititInstallmentPlanNumber(), false
            );
            if ($paymentAction == "authorize_capture") {
                $payment->setShouldCloseParentTransaction(true);
                $payment->setIsTransactionClosed(1);
                $payment->registerCaptureNotification($grandTotal);
                $orderObj->addStatusToHistory(
                        false, 'Payment NotifyOrderShipped was sent with number ID: ' . $this->checkoutSession->getSplititInstallmentPlanNumber(), false
                );
            }
            /*$orderObj->queueNewOrderEmail();
            $orderObj->sendNewOrderEmail();*/
            $this->orderSender->send($orderObj);
            $orderObj->save();

            $this->logger->addDebug('====== Order Id =====:' . $orderId . '==== Order Increment Id ======:' . $orderIncrementId);

            $this->_redirect("checkout/onepage/success")->sendResponse();
        } else {

            $this->logger->addDebug('====== Order cancel due to Grand total and Payment detail total coming from Api is not same. =====');
            $cancelResponse = $this->paymentForm->cancelInstallmentPlan($this->api, $params["InstallmentPlanNumber"]);
            if ($cancelResponse["status"]) {
                $this->_redirect("splititpaymentmethod/payment/cancel")->sendResponse();
            }
        }
    }

}
