<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
 
class Productlist extends \Magento\Framework\App\Action\Action {

	 public function execute() {
	 	$params = $this->getRequest()->getParams();
	 	$result = array();
	    $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
	 	if(isset($params['isAjax'])&&$params['isAjax']){
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $Productlist = $objectManager->get('\Splitit\Paymentmethod\Model\Source\Productskus');
	        if((isset($params['term'])&&$params['term'])||(isset($params['prodIds'])&&$params['prodIds'])){
	        	$result = $Productlist->toOptionArray($params);
	        }
	 	}
	 	$resultJson->setData($result);
	 	return $resultJson;
    }

    
}