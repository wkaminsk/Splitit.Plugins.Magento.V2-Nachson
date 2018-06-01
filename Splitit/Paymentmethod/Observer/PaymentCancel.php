<?php

namespace Splitit\Paymentmethod\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaymentCancel implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $objectManager;
    protected $_paymentModel;
    protected $_apiModel;
    protected $customerSession;
    protected $_logger;

    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Splitit\Paymentmethod\Model\Payment $paymentModel,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
        $this->customerSession = $customerSession;
        $this->_paymentModel = $paymentModel;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_apiModel = $this->objectManager->get('Splitit\Paymentmethod\Model\Api');
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
        $this->_logger->debug(get_class($payment));
        $transactionId = $payment->getParentTransactionId();
        $this->_logger->debug('transactionId='.$transactionId);
        try {
            $apiLogin = $this->_apiModel->apiLogin();
            $api = $this->_apiModel->getApiUrl();
            if($payment->getAuthorizationTransaction()){
            $installmentPlanNumber = $payment->getAuthorizationTransaction()->getTxnId();
            $this->_logger->debug('IPN='.$installmentPlanNumber);
            $ipn = substr($installmentPlanNumber, 0, strpos($installmentPlanNumber, '-'));
            if($ipn != ""){
                $installmentPlanNumber = $ipn;
            }
            $params = array(
                "RequestHeader" => array(
                    "SessionId" => $this->customerSession->getSplititSessionid(),
                ),
                "InstallmentPlanNumber" => $installmentPlanNumber,
                "RefundUnderCancelation" => "OnlyIfAFullRefundIsPossible"
            );

            $result = $this->_apiModel->makePhpCurlRequest($api, "InstallmentPlan/Cancel",$params);
            $result = json_decode($result, true);
            if (isset($result["ResponseHeader"])&&isset($result["ResponseHeader"]["Errors"])&&!empty($result["ResponseHeader"]["Errors"])) {
                $errorMsg = "";
                
                $errorCode = 503;
                $isErrorCode503Found = 0;
                foreach ($result["ResponseHeader"]["Errors"] as $key => $value) {
                    $errorMsg .= $value["ErrorCode"]." : ".$value["Message"];
                    if($value["ErrorCode"] == $errorCode){
                        $isErrorCode503Found = 1;
                        break;
                    }
                }    
                
                
                if($isErrorCode503Found == 0){
                    $this->_logger->error(__($errorMsg));
                    throw new \Magento\Framework\Validator\Exception(__($errorMsg));
                }

            }elseif(isset($result["serverError"])){
                $errorMsg = $result["serverError"];
                $this->_logger->error(__($errorMsg));
                throw new \Magento\Framework\Validator\Exception(__($errorMsg));
            }
            }
        } catch (\Exception $e) {
            $this->_logger->debug(['transaction_id' => $transactionId, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment cancel error.'));
        }
        return $this;
    }
}