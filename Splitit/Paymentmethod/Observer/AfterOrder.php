<?php

namespace Splitit\Paymentmethod\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterOrder implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    protected $objectManager;
    protected $paymentForm;
    protected $api;
    protected $_logger;
    protected $orderSender;
    protected $customerSession;
    /**
     * @var \Splitit\Paymentmethod\Helper\Data
     */
    protected $_helperData;

    /**
     * AfterOrder constructor.
     * @param \Splitit\Paymentmethod\Model\PaymentForm $paymentForm,
     * @param \Splitit\Paymentmethod\Model\Api $api,
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Checkout\Model\Session $_checkoutSession,
     * @param \Splitit\Paymentmethod\Helper\Data $helperData,
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Splitit\Paymentmethod\Model\PaymentForm $paymentForm,
        \Splitit\Paymentmethod\Model\Api $api,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Splitit\Paymentmethod\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
        $this->paymentForm = $paymentForm;
        $this->api = $api;
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->_helperData = $helperData;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->orderSender = $this->objectManager->get('Magento\Sales\Model\Order\Email\Sender\OrderSender');
    }

    /**
     * Set fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order=$observer->getEvent()->getOrder();
        $payment=$order->getPayment();
        
        $this->_logger->debug("CLASS===".get_class($payment));

        $additional_data = $payment->getAdditionalInformation();

        $this->_logger->debug("additional_data_function===".print_r($additional_data,true));

        $InstallmentPlanNumber = $additional_data['InstallmentPlanNumber'];
        if(!$InstallmentPlanNumber){
            throw new \Magento\Framework\Validator\Exception(__("InstallmentPlanNumber not found"), \Exception("InstallmentPlanNumber not found",402));
        }
        // $extensionAttributes = $order->getExtensionAttributes();
        // $this->_logger->debug("extensionAttributes===".print_r($extensionAttributes,true));
        $this->_logger->debug('order_id='.get_class($order));
        $this->_logger->debug("order_data===".$order->getEntityId()."===".$order->getIncrementId()."====".$order->getGrandTotal());

        $transactionId = $payment->getParentTransactionId();
        $this->_logger->debug('transactionId='.$transactionId);
        
        try {
            $api = $this->paymentForm->_initApi();
            $this->customerSession->setInstallmentPlanNumber($InstallmentPlanNumber);
            $this->_checkoutSession->setSplititInstallmentPlanNumber($InstallmentPlanNumber);

            $planDetails = $this->paymentForm->getInstallmentPlanDetails($this->api);

            $this->_logger->debug('======= get installmentplan details :  ======= ');
            $this->_logger->debug(print_r($planDetails,TRUE));

            $orderId=$order->getEntityId();
            $orderIncrementId = $order->getIncrementId();
            // $orderObj = $this->order->load($orderId);
            $grandTotal = number_format((float) $order->getGrandTotal(), 2, '.', '');
            $planDetails["grandTotal"] = number_format((float) $planDetails["grandTotal"], 2, '.', '');
            $this->_logger->debug('======= grandTotal(order):' . $grandTotal . ', grandTotal(planDetails):' . $planDetails["grandTotal"] . '   ======= ');
            if ($grandTotal == $planDetails["grandTotal"] && ($planDetails["planStatus"] == "PendingMerchantShipmentNotice" || $planDetails["planStatus"] == "InProgress")) {

                // $payment = $order->getPayment();
                $paymentAction = $this->_helperData->getConfig("payment/splitit_paymentredirect/payment_action");
                
                $this->_logger->debug("setTransactionId");
                $payment->setTransactionId($InstallmentPlanNumber);
                
                $this->_logger->debug("setParentTransactionId");
                $payment->setParentTransactionId($InstallmentPlanNumber);
                
                $this->_logger->debug("setInstallmentsNo");
                $payment->setInstallmentsNo($planDetails["numberOfInstallments"]);
                
                $this->_logger->debug("setIsTransactionClosed");
                $payment->setIsTransactionClosed(0);
                
                $this->_logger->debug("setCurrencyCode");
                $payment->setCurrencyCode($planDetails["currencyCode"]);
                
                $this->_logger->debug("setCcType");
                $payment->setCcType($planDetails["cardBrand"]["Code"]);
                
                $this->_logger->debug("setIsTransactionApproved");
                $payment->setIsTransactionApproved(true);

                $this->_logger->debug("registerAuthorizationNotification");
                $payment->registerAuthorizationNotification($grandTotal);
    //            $payment->setAdditionalInformation(
    //                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $planDetails]
    //            );
                $this->_logger->debug("addStatusToHistory");
                $order->addStatusToHistory(
                        $order->getStatus(), 'Payment InstallmentPlan was created with number ID: '
                        . $InstallmentPlanNumber, false
                );
                if ($paymentAction == "authorize_capture") {
                    $this->_logger->debug("setShouldCloseParentTransaction");
                    $payment->setShouldCloseParentTransaction(true);
                    
                    $this->_logger->debug("setIsTransactionClosed");
                    $payment->setIsTransactionClosed(1);
                    
                    $this->_logger->debug("registerCaptureNotification");
                    $payment->registerCaptureNotification($grandTotal);
                    
                    $this->_logger->debug("addStatusToHistory");
                    $order->addStatusToHistory(
                            false, 'Payment NotifyOrderShipped was sent with number ID: ' . $InstallmentPlanNumber, false
                    );
                }
                //$orderObj->queueNewOrderEmail();
    //            $orderObj->sendNewOrderEmail();
                $this->_logger->debug("orderSender->send");
                // $this->orderSender->send($order);
                $this->_logger->debug("==payment save==");
                // $payment->save();
                // $order->save();

                $this->_logger->debug('====== Order Id =====:' . $orderId . '==== Order Increment Id ======:' . $orderIncrementId);
                $this->_logger->debug("==updateRefOrderNumber==");
                $curlRes = $this->paymentForm->updateRefOrderNumber($this->api, $order);
                $this->_logger->debug('====== Order Id =====:' . $orderId . '==== Order Increment Id ======:' . $orderIncrementId."===updated on splitit");

            } else {

                $this->_logger->debug('====== Order cancel due to Grand total and Payment detail total coming from Api is not same. =====');
                $cancelResponse = $this->paymentForm->cancelInstallmentPlan($this->api, $InstallmentPlanNumber);
                if ($cancelResponse["status"]) {
                    throw new \Magento\Framework\Validator\Exception("Order cancel due to Grand total and Payment detail total coming from Api is not same.", 402);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->addDebug("ashwani===",['transaction_id' => $transactionId, 'IPN'=>$InstallmentPlanNumber, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment cancel error.'));
            throw new \Magento\Framework\Validator\Exception(__("Error occured while updating the order."), $e);
        }
        return $this;
    }
}
