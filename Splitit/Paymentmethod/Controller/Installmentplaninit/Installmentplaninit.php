<?php
/**
 * Copyright © 2015 Splitit d.o.o.
 * created by Zoran Salamun(zoran.salamun@Splitit.net)
 */
namespace Splitit\Paymentmethod\Controller\Installmentplaninit;
use Magento\Framework\Controller\ResultFactory;

class Installmentplaninit extends \Magento\Framework\App\Action\Action {

	private $helper;

	public function execute(){

		$this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$request = $this->_objectManager->get('\Magento\Framework\App\Request\Http')->getParams();
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$response = [
                        "status" => false,
                        "errorMsg" => "",
                        "successMsg"=>"",
                        "data" => "",
                        
        ];

		$selectedInstallment = "";
		if(isset($request["selectedInstallment"]) && $request["selectedInstallment"] != ""){
			$selectedInstallment = $request["selectedInstallment"];	
		}else{
			$response["errorMsg"] = "Please select Number of Installments";
			return $resultJson->setData($response);
			
		}
		$guestEmail = "";
		if(isset($request["guestEmail"])){
			$guestEmail = $request["guestEmail"];	
		}

		$apiModelObj = $this->_objectManager->get('Splitit\Paymentmethod\Model\Api');
		$loginResponse = $apiModelObj->apiLogin();
		// check if login successfully or not
		if(!$loginResponse["status"]){
			$response["errorMsg"] = $loginResponse["errorMsg"];
			return $resultJson->setData($response);
			
		}
		// call Installment Plan
		$installmentPlanInitResponse = $apiModelObj->installmentPlanInit($selectedInstallment, $guestEmail);

		if($installmentPlanInitResponse["status"]){
            $response["status"] = true;
            $response["successMsg"] = $installmentPlanInitResponse["successMsg"];
        }else{
            $response["errorMsg"] = $installmentPlanInitResponse["errorMsg"];
        }
        
        	$resultJson->setData($response);
	        return $resultJson;

	}

}