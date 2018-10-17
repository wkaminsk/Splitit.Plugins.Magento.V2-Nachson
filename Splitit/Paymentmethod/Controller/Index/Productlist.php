<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Index;
 
class Productlist extends \Magento\Framework\App\Action\Action {

	 public function execute() {
	 	$params = $this->getRequest()->getParams();
	 	$result = array();
	 	if(isset($params['isAjax'])&&$params['isAjax']){
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $Productlist = $objectManager->get('\Splitit\Paymentmethod\Model\Source\Productskus');
	        if((isset($params['term'])&&$params['term'])||(isset($params['prodIds'])&&$params['prodIds'])){
	        	$result = $Productlist->toOptionArray($params);
	        }
	 	}
	 	echo json_encode($result);
	 	return;
    }

    
}