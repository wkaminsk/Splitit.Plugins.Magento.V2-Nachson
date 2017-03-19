<?php
 
namespace Inchoo\Stripe\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
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
       
}