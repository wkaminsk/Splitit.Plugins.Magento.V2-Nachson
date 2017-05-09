<?php
/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */
namespace Splitit\Paymentmethod\Controller\Showinstallmentprice;
use Magento\Framework\Controller\ResultFactory;
class Getinstallmentprice extends \Magento\Framework\App\Action\Action {

	private $helper;
	

	public function execute() {
		$this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$response = [
                        "status" => true,
                        "isActive" => "",
                        "pageType"=>"",
                        "displayInstallmentPriceOnPage" => "",
                        "numOfInstallmentForDisplay" => "",
                        "installmetPriceText" => "",
                        "grandTotal" => "",
                        "currencySymbol" => ""
                        
        ];

		$isEnable = $this->helper->getConfig("payment/splitit_paymentmethod/enable_installment_price");
		if($isEnable == ""){
			$isEnable = 0;
		}
        $displayInstallmentPriceOnPage = $this->helper->getConfig("payment/splitit_paymentmethod/installment_price_on_pages");
        $numOfInstallmentForDisplay = $this->helper->getConfig("payment/splitit_paymentmethod/installments_count");
        $installmetPriceText = $this->helper->getConfig("payment/splitit_paymentmethod/installment_price_text");
        if(is_null($installmetPriceText)){
            $installmetPriceText = "";
        }

        $response["isActive"] = $isEnable;
        $response["displayInstallmentPriceOnPage"] = $displayInstallmentPriceOnPage;
        $response["numOfInstallmentForDisplay"] = $numOfInstallmentForDisplay;
        $response["installmetPriceText"] = $installmetPriceText;

        $cart = $this->_objectManager->get("\Magento\Checkout\Model\Cart");
		$totalAmount = $cart->getQuote()->getGrandTotal();
		$response["grandTotal"] = number_format((float)$totalAmount, 2, '.', '');	
		$response["currencySymbol"] = $this->helper->getCurrencyData();
        
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($response);
        /*echo $data = $this->helper->encodeData($response);
		return;*/

        

    	
    }

    


    
}