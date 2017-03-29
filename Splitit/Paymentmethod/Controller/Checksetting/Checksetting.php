<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Checksetting;
use Magento\Framework\Controller\ResultFactory;

class Checksetting extends \Magento\Framework\App\Action\Action {

	private $helper;

	public function execute(){

		$response = [
                        "status" => false,
                        "errorMsg" => "",
                        "successMsg"=>"",
                        
        ];
        $this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$apiModelObj = $this->_objectManager->get('Splitit\Paymentmethod\Model\Api');
		$loginResponse = $apiModelObj->apiLogin();

		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		if(!$loginResponse["status"]){
			$response["errorMsg"] = $loginResponse["errorMsg"];
			$resultJson->setData($response);
        	return $resultJson;
		}
		if($this->helper->getConfig("payment/splitit_paymentmethod/sandbox_flag")){
			$response["successMsg"] = "[Sandbox Mode] Successfully login! API available!";	
		}else{
			$response["successMsg"] = "[Production Mode] Successfully login! API available!";	
		}
		$response["status"] = true;
		
		
    	
        $resultJson->setData($response);
        return $resultJson;
        

	}

}