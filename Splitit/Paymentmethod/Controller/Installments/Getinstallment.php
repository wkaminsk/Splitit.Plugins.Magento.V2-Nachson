<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Installments;

class Getinstallment extends \Magento\Framework\App\Action\Action {

	private $helper;
	/*public function __construct(){

	    $this->helper = $jsonHelper;
	}
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
	*/

	public function execute() {
		$this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$installments = $this->helper->getConfig("payment/splitit_paymentmethod/fixed_installment");

		$cart = $this->_objectManager->get("\Magento\Checkout\Model\Cart");
		$grandTotal = $cart->getQuote()->getGrandTotal();

		$currencySymbol = $this->_objectManager->get('\Magento\Directory\Model\Currency')->getCurrencySymbol();

		$installmentHtml = '<option value="">--No Intallment available--</option>';
		if(count($installments)){
			$installmentHtml = '<option value="">--Please Select--</option>';
			foreach (explode(',', $installments) as $value) {
				$installmentHtml .= '<option value="'.$value.'">'.$value.' Installments of '.$currencySymbol.round($grandTotal/$value,2).'</option>';
			}
			
		}
		echo $data = $this->helper->encodeData($installmentHtml);
		return;
    	
    }



    
}