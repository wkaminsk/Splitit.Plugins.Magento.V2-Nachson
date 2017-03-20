<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Inchoo\Stripe\Controller\Installmentplaninit;
use Magento\Framework\Controller\ResultFactory;

class Installmentplaninit extends \Magento\Framework\App\Action\Action {

	private $helper;

	public function execute(){

		$this->helper = $this->_objectManager->create('Inchoo\Stripe\Helper\Data');
		$request = $this->_objectManager->get('\Magento\Framework\App\Request\Http')->getParams();
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
			echo $data = $this->helper->encodeData($response);
			return;
		}
		$guestEmail = $request["guestEmail"];


		$apiModelObj = $this->_objectManager->get('Inchoo\Stripe\Model\Api');
		$loginResponse = $apiModelObj->apiLogin();
		// check if login successfully or not
		if(!$loginResponse["status"]){
			$response["errorMsg"] = $loginResponse["errorMsg"];
			echo $data = $this->helper->encodeData($response);
			return;
		}
		// call Installment Plan
		$installmentPlanInitResponse = $apiModelObj->installmentPlanInit($selectedInstallment, $guestEmail);

		if($installmentPlanInitResponse["status"]){
            $response["status"] = true;
            $response["successMsg"] = $installmentPlanInitResponse["successMsg"];
        }else{
            $response["errorMsg"] = $installmentPlanInitResponse["errorMsg"];
        }
        try{
        	$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
	        $resultJson->setData($response);
	        return $resultJson;
        	//echo $data = json_encode($response);
        }catch(Exception $e){
        	echo $e->getMessage();
        }
        
		return;

	}

}