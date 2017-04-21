<?php
 
namespace Splitit\Paymentmethod\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Directory\Model\Currency;

//use \Magento\Framework\Json\Helper\Data;
class Data extends AbstractHelper
{

	
	public function getConfig($config_path)
	{
	    return $this->scopeConfig->getValue(
	            $config_path,
	            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
	            );
	}

	public function encodeData($dataToEncode){
    	
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 		$jsonObject = $objectManager->create('\Magento\Framework\Json\Helper\Data');
	    $encodedData = $jsonObject->jsonEncode($dataToEncode);
	    return $encodedData;
	}

	public function getCurrencyData(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$currencyCode = $storeManager->getStore()->getCurrentCurrencyCode(); 
		$currencyRate = $storeManager->getStore()->getCurrentCurrencyRate(); 
		
		$currency = $objectManager->create('Magento\Directory\Model\Currency')->load($currencyCode); 
		return $currencySymbol = $currency->getCurrencySymbol(); 
		
	}
       
}