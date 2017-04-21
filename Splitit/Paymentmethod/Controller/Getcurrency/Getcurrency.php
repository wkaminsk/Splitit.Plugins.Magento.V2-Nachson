<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Getcurrency;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Directory\Model\Currency;

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
		$response["currencyCodeSymbol"] = $this->getAllavailableCurrencies();
		
    	$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
        

	}

	private function getAllavailableCurrencies(){
		$storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$allAvailableCurrencyCodes = $storeManager->getStore()->getAvailableCurrencyCodes(false);
		$currency = $this->_objectManager->get('Magento\Directory\Model\Currency');
		$currencyCodeSymbol = [];
		foreach ($allAvailableCurrencyCodes as $key => $value) {
			$currencyCodeSymbol[$value] = $currency->load($value)->getCurrencySymbol();
		}
		return $currencyCodeSymbol;
	}

}