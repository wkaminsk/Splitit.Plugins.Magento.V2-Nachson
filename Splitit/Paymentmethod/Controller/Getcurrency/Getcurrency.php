<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Getcurrency;
use Magento\Framework\Controller\ResultFactory;

class Getcurrency extends \Magento\Framework\App\Action\Action {

	private $helper;

	public function execute(){

		$this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$request = $this->_objectManager->get('\Magento\Framework\App\Request\Http')->getParams();
		$response = [
                        "status" => false,
                        "errorMsg" => "",
                        "successMsg"=>"",
                        "currencySymbol" => "",
                        "currencyCode" => ""
                        
        ];
		$currencySymbol = $this->_objectManager->get('\Magento\Directory\Model\Currency')->getCurrencySymbol();
		$currencyCode = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseCurrencyCode();
		$response["currencySymbol"] = $currencySymbol;
		$response["currencyCode"] = $currencyCode;
		
    	$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
        

	}

}