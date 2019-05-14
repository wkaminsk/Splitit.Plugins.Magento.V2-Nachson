<?php

namespace Splitit\Paymentmethod\Model;

use Splitit\Paymentmethod\Api\InitInterface;
use Splitit\Paymentmethod\Model\PaymentForm;

class Init implements InitInterface {

    protected $api;
    protected $helper;
    protected $jsonHelper;
    protected $_store;
    protected $objectManager;
    protected $logger;
    protected $paymentForm;

	public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Splitit\Paymentmethod\Model\Api $api,
        \Magento\Store\Api\Data\StoreInterface $store,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        PaymentForm $PaymentForm
    ) {
    	$this->api = $api;
    	$this->paymentForm = $PaymentForm;
        $this->_store = $store;
        $this->urlBuilder = $urlBuilder;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->objectManager=$objectManager;
        $this->helper = $objectManager->get('Splitit\Paymentmethod\Helper\Data');
    }

	/**
     * Returns Splitit API response
     *
     * @api
     * @param mixed $data init Params.
     * @return mixed Splitit API reponse
     */
    public function initSplitit($data){
    	try{
	    	$response = $this->paymentForm->_initApi();
	    	$params = $data;
	    	$params["RequestHeader"] = array(
	                "SessionId" => $response["splititSessionId"],
	                "ApiKey"    => $this->helper->getConfig("payment/splitit_paymentredirect/api_terminal_key")
	            );
	    	/*return $this->jsonHelper->jsonEncode($params);*/
	    	$paymentAction = $this->helper->getConfig("payment/splitit_paymentredirect/payment_action");
	    	$autoCapture = false;
	    	if ($paymentAction == "authorize_capture") {
	    	    $autoCapture = true;
	    	}
	    	$params['PlanData']['AutoCapture'] = $autoCapture;
	    	// check for 3d secure yes or no
	    	$_3d_secure = $this->helper->getConfig("payment/splitit_paymentredirect/splitit_3d_secure");
	    	$_3d_minimal_amount = $this->helper->getConfig("payment/splitit_paymentredirect/splitit_3d_minimal_amount");
	    	$grandTotal = $params['PlanData']['Amount']['Value'];
	    	if($_3d_secure != "" && $_3d_secure == 1 && $_3d_minimal_amount != "" && $grandTotal >= $_3d_minimal_amount){
	    	    $params['PlanData']["Attempt3DSecure"] = true;
	    	    $params["RedirectUrls"]= array(
	    	        "Succeeded"=> $params['PaymentWizardData']['SuccessExitURL'],
	    	        "Failed"=> $params['PaymentWizardData']['CancelExitURL'],
	    	        "Canceled"=> $params['PaymentWizardData']['CancelExitURL']
	    	    );
	    	}
	    	/*$this->jsonHelper->jsonDecode($result);*/
	    	$result = $this->api->installmentplaninitforhostedsolution($params);
    		return $this->jsonHelper->jsonEncode(['session_id'=>$response["splititSessionId"],'initData'=>$result]);
    	} catch (\Exception $e) {
    		return $this->jsonHelper->jsonEncode(['success'=>false,'error'=>true,'error_msg'=>$e->getMessage()]);
    	}
    }
}