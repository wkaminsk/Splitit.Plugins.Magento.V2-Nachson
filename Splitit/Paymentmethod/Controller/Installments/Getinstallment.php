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
		$response = [
                        "status" => true,
                        "errorMsg" => "",
                        "successMsg"=>"",
                        "installmentHtml" => "",
                        "helpSection" => ""
                        
        ];

		$cart = $this->_objectManager->get("\Magento\Checkout\Model\Cart");
		$totalAmount = $cart->getQuote()->getGrandTotal();

		$selectInstallmentSetup = $this->helper->getConfig('payment/splitit_paymentmethod/select_installment_setup');
		$options = $this->_objectManager->get('Splitit\Paymentmethod\Model\Source\Installments')->toOptionArray();
		$currencySymbol = $this->_objectManager->get('\Magento\Directory\Model\Currency')->getCurrencySymbol();

		$installmentHtml = '<option value="">--No Intallment available--</option>';
		if($selectInstallmentSetup == "" || $selectInstallmentSetup == "fixed"){
			$installments = $this->helper->getConfig("payment/splitit_paymentmethod/fixed_installment");

			

			
			if(count($installments)){
				$installmentHtml = '<option value="">--Please Select--</option>';
				foreach (explode(',', $installments) as $value) {
					$installmentHtml .= '<option value="'.$value.'">'.$value.' Installments of '.$currencySymbol.round($totalAmount/$value,2).'</option>';
				}
				
			}
		}else{
			$depandingOnCartInstallments = $this->helper->getConfig("payment/splitit_paymentmethod/depanding_on_cart_total_values");
			$depandingOnCartInstallmentsArr = json_decode($depandingOnCartInstallments);
			$dataAsPerCurrency = [];
            foreach($depandingOnCartInstallmentsArr as $data){
                $dataAsPerCurrency[$data->doctv->currency][] = $data->doctv;
            }
            $currentCurrencyCode = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseCurrencyCode();
            if(count($dataAsPerCurrency) && isset($dataAsPerCurrency[$currentCurrencyCode])){
                
                foreach($dataAsPerCurrency[$currentCurrencyCode] as $data){
                    if($totalAmount >= $data->from && !empty($data->to) && $totalAmount <= $data->to){
                        foreach (explode(',', $data->installments) as $n) {
                            $installmentHtml .= '<option value="'.$n.'">'.$n.' Installments of '.$currencySymbol.round($totalAmount/$n,2).'</option>';
                        }
                        break;
                    }else if($totalAmount >= $data->from && empty($data->to)){
                        foreach (explode(',', $data->installments) as $n) {

                            $installmentHtml .= '<option value="'.$n.'">'.$n.' Installments of '.$currencySymbol.round($totalAmount/$n,2).'</option>';
                        }
                        break;
                    }
                }
            }

		}
		$response["installmentHtml"] = $installmentHtml;
		$response["helpSection"] = $this->getHelpSection();
		echo $data = $this->helper->encodeData($response);
		return;
    	
    }

    private function getHelpSection(){
    	$baseUrl = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
    	$help = [];

    	if($this->helper->getConfig("payment/splitit_paymentmethod/faq_link_enabled")){
    		$help["title"] = $this->helper->getConfig("payment/splitit_paymentmethod/faq_link_title");
    		$help["link"] = $baseUrl."splititpaymentmethod/help/help"; 
    	}
    	return $help;
    }

	/*public function execute() {
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
    	
    }*/



    
}