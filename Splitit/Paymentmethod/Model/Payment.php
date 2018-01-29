<?php
/**
 * Payment payment method model
 *
 * @category    Splitit
 * @package     Splitit_Paymentmethod
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   Splitit (http://Splitit.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Splitit\Paymentmethod\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Payment\Model\IframeConfigProvider;
use Magento\Store\Model\StoreManagerInterface;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'splitit_paymentmethod';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;

    protected $_paymentApi = false;

    protected $_countryFactory;

    //protected $_minAmount = null;
    //protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('USD');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

    protected $_apiModel = null;
    private $customerSession;
    private $helper;
    private $objectManager = null;
    private $grandTotal = null;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;

        

        //$this->_minAmount = $this->getConfigData('min_order_total');
        //$this->_maxAmount = $this->getConfigData('max_order_total');

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_apiModel = $this->objectManager->get('Splitit\Paymentmethod\Model\Api');
        $this->customerSession = $customerSession;
        $this->helper = $this->objectManager->get('Splitit\Paymentmethod\Helper\Data');
        $cart = $this->objectManager->get("\Magento\Checkout\Model\Cart");
        $this->grandTotal = round($cart->getQuote()->getGrandTotal(),2);
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        
        /*if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }*/
        
        try{
           
            $api = $this->_apiModel->getApiUrl();
            $result = $this->createInstallmentPlan($api, $payment, $amount);
            $result = json_decode($result, true);

            // show error if there is any error from spliti it when click on place order
            if(!$result["ResponseHeader"]["Succeeded"]){
                $errorMsg = "";
                if(isset($result["serverError"])){
                    $errorMsg = $result["serverError"];
                    $this->_logger->error(__($errorMsg));
                    throw new \Magento\Framework\Validator\Exception(__($errorMsg));
                     
                }else{
                    foreach ($result["ResponseHeader"]["Errors"] as $key => $value) {
                    $errorMsg .= $value["ErrorCode"]." : ".$value["Message"];
                    }
                    $this->_logger->error(__($errorMsg));
                    throw new \Exception('your error message');
                    throw new \Magento\Framework\Validator\Exception(__($errorMsg));        
                }
                
            }
            $payment->setTransactionId($result['InstallmentPlan']['InstallmentPlanNumber']);
            $payment->setIsTransactionClosed(0);
            $payment->setIsTransactionApproved(true);
            foreach (
                array(
                    'ConsumerFullName',
                    'Email',
                    'Amount',
                    'InstallmentNumber'
                ) as $param) {

                unset($result[$param]);

            }
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $result]
            );

            $order = $payment->getOrder();
            
            $order->addStatusToHistory(" ",
                'Payment InstallmentPlan was created with number ID: '
                . $result['InstallmentPlan']['InstallmentPlanNumber'].' with No# of Installments: '.$this->customerSession->getSelectedInstallment(),
                false
            );

            // call InstallmentPlan-UpdatePlan-Params for update "RefOrderNumber" after order creation
            $updateStatus = $this->updateRefOrderNumber($api, $order);        
            if($updateStatus["status"] == false){

                $this->_logger->error(__($updateStatus["errorMsg"]));
                    throw new \Magento\Framework\Validator\Exception(__($updateStatus["errorMsg"]));
                    
            }
        }catch(\Exception $e){
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment Authorize error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment Authorize error.'));
        }
        


        return $this;
    }


    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try{
            if (!$payment->getAuthorizationTransaction()) {
                $this->authorize($payment, $amount);
                $authNumber = $payment->getTransactionId();
            } else {
                $authNumber = $payment->getAuthorizationTransaction()->getTxnId();
            }
            $paymentAction = $this->helper->getConfig("payment/splitit_paymentmethod/payment_action");
            $params = array('InstallmentPlanNumber' => $authNumber);
            if($paymentAction == "authorize_capture"){
                $api = $this->_apiModel->getApiUrl();
                $sessionId = $this->customerSession->getSplititSessionid();
            }else{
                $api = $this->_apiModel->apiLogin();
                $sessionId = $this->customerSession->getSplititSessionid();
            }
            $params = array_merge($params, array("RequestHeader"=> array('SessionId' => $sessionId)));
            $api = $this->_apiModel->getApiUrl();
            $result = $this->_apiModel->makePhpCurlRequest($api, "InstallmentPlan/StartInstallments",$params);
            $result = json_decode($result, true);
            if (!$result) {
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
            $payment->setIsTransactionClosed(1);
            $order = $payment->getOrder();

            $order->addStatusToHistory(
                false,
                'Payment NotifyOrderShipped was sent with number ID: '.$authNumber, false
            );
            $order->save();

        } catch (\Exception $e) {
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }

        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $transactionId = $payment->getParentTransactionId();
        try {
            $apiLogin = $this->_apiModel->apiLogin();
            $api = $this->_apiModel->getApiUrl();
            $installmentPlanNumber = $payment->getAuthorizationTransaction()->getTxnId();
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
            if (!$result) {
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
        } catch (\Exception $e) {
            $this->debugData(['transaction_id' => $transactionId, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment cancel error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment cancel error.'));
        }

        return $this;
    }

    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $transactionId = $payment->getParentTransactionId();
        try {
            $apiLogin = $this->_apiModel->apiLogin();
            $api = $this->_apiModel->getApiUrl();
            $installmentPlanNumber = $payment->getAuthorizationTransaction()->getTxnId();
            $ipn = substr($installmentPlanNumber, 0, strpos($installmentPlanNumber, '-'));
            if($ipn != ""){
                $installmentPlanNumber = $ipn;
            }
            $params = array(
                "RequestHeader" => array(
                    "SessionId" => $this->customerSession->getSplititSessionid(),
                ),
                "InstallmentPlanNumber" => $installmentPlanNumber,
                "Amount" => array("Value" => $amount),
                "_RefundStrategy" => "FutureInstallmentsFirst"

            );

            $result = $this->_apiModel->makePhpCurlRequest($api, "InstallmentPlan/Refund",$params);
            $result = json_decode($result, true);
            if (!$result) {
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
        } catch (\Exception $e) {
            $this->debugData(['transaction_id' => $transactionId, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment refunding error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment refunding error.'));
        }

        $payment
        ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
        ->setParentTransactionId($transactionId)
        ->setIsTransactionClosed(1)
        ->setShouldCloseParentTransaction(1);

        return $this;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {//return parent::isAvailable($quote);
        if($this->checkAvailableInstallments($quote)){
            return parent::isAvailable($quote);
        }else{
            return false;
        }
    /*return parent::isAvailable($quote);
        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        if (!$this->getConfigData('api_key')) {
            return false;
        }

        return parent::isAvailable($quote);*/
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        /*if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }*/
        return true;
    }

    protected function createInstallmentPlan($api, $payment, $amount)
    {
        $params = [
            "RequestHeader" => [
                "SessionId" => $this->customerSession->getSplititSessionid(),
                "ApiKey"    => $this->helper->getConfig("payment/splitit_payment/api_terminal_key"),
            ],
            "InstallmentPlanNumber" => $this->customerSession->getInstallmentPlanNumber(),
            "CreditCardDetails" => [
                "CardCvv" => $payment->getCcCid(),
                "CardNumber" => $payment->getCcNumber(),
                "CardExpYear" => $payment->getCcExpYear(),
                "CardExpMonth" => $payment->getCcExpMonth(),
            ],
            "PlanApprovalEvidence" => [
                "AreTermsAndConditionsApproved" => "True"
            ],
        ];

        $result = $this->_apiModel->makePhpCurlRequest($api, "InstallmentPlan/Create",$params);
        
        return $result;
    }

    public function updateRefOrderNumber($api, $order){
        $params = [
            "RequestHeader" => [
                "SessionId" => $this->customerSession->getSplititSessionid(),
            ],
            "InstallmentPlanNumber" => $this->customerSession->getInstallmentPlanNumber(),
            "PlanData" => [
                "ExtendedParams" => [
                    "CreateAck" => "Received",
                ],
                "RefOrderNumber" => $order->getIncrementId(),
            ],
        ];
        $response = ["status"=>false, "errorMsg" => ""];
        $result = $this->_apiModel->makePhpCurlRequest($api, "InstallmentPlan/Update",$params);
        //$result = $this->_apiModel->makePhpCurlRequestForUpdate($api, "InstallmentPlan/Update",$params);
        $decodedResult = json_decode($result, true);
        if(isset($decodedResult["ResponseHeader"]["Succeeded"]) && $decodedResult["ResponseHeader"]["Succeeded"] == 1){
            $response["status"] = true;
        }else if(isset($decodedResult["ResponseHeader"]) && count($decodedResult["ResponseHeader"]["Errors"])){
            $errorMsg = "";
            $i = 1;
            foreach ($decodedResult["ResponseHeader"]["Errors"] as $key => $value) {
                $errorMsg .= "Code : ".$value["ErrorCode"]." - ".$value["Message"];
                if($i < count($decodedResult["ResponseHeader"]["Errors"])){
                    $errorMsg .= ", ";
                }
                $i++;
            }

            $response["errorMsg"] = $errorMsg;
        }
        return $response;
        
    }

    public function checkAvailableInstallments($quote){
        $installments = array();
        $totalAmount = $this->grandTotal;
        $selectInstallmentSetup = $this->getConfigData('select_installment_setup');
        
        $options = $this->objectManager->get('Splitit\Paymentmethod\Model\Source\Installments')->toOptionArray();
        
        $depandOnCart = 0;
        
        if($selectInstallmentSetup == "" || $selectInstallmentSetup == "fixed"){ // Select Fixed installment setup
            
            $fixedInstallments = $this->helper->getConfig("payment/splitit_paymentmethod/fixed_installment");
            $installments = explode(',', $fixedInstallments);
            if(count($installments) > 0){
                return true;
            }
            
        }else{ // Select Depanding on cart installment setup
            $depandOnCart = 1;  
            $depandingOnCartInstallments = $this->helper->getConfig("payment/splitit_paymentmethod/depanding_on_cart_total_values");
            $depandingOnCartInstallmentsArr = json_decode($depandingOnCartInstallments);
            $dataAsPerCurrency = [];
            foreach($depandingOnCartInstallmentsArr as $data){
                $dataAsPerCurrency[$data->doctv->currency][] = $data->doctv;
            }
            $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface'); 
            $currentCurrencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
            if(count($dataAsPerCurrency) && isset($dataAsPerCurrency[$currentCurrencyCode])){
                
                foreach($dataAsPerCurrency[$currentCurrencyCode] as $data){
                    if($totalAmount >= $data->from && !empty($data->to) && $totalAmount <= $data->to){
                        foreach (explode(',', $data->installments) as $n) {
                            if((array_key_exists($n, $options))){
                                $installments[$n] = $n;
                            }
                        }
                        break;
                    }else if($totalAmount >= $data->from && empty($data->to)){
                        foreach (explode(',', $data->installments) as $n) {

                            if((array_key_exists($n, $options))){
                                $installments[$n] = $n;  
                            }
                        }
                        break;
                    }
                }
            }
            if(count($installments) > 0){
                return true;
            }
        } 

        return false;

    }
}