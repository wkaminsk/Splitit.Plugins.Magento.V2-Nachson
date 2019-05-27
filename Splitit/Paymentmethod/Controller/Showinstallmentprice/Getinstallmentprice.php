<?php

/**
 * Copyright © 2015 Inchoo d.o.o.
 * created by Zoran Salamun(zoran.salamun@inchoo.net)
 */

namespace Splitit\Paymentmethod\Controller\Showinstallmentprice;

use Magento\Framework\Controller\ResultFactory;

class Getinstallmentprice extends \Magento\Framework\App\Action\Action {

	private $helper;
	private $payment;
	private $paymentForm;

	public function execute() {
		$this->helper = $this->_objectManager->create('Splitit\Paymentmethod\Helper\Data');
		$this->payment = $this->_objectManager->create('Splitit\Paymentmethod\Model\Payment');
		$this->paymentForm = $this->_objectManager->create('Splitit\Paymentmethod\Model\PaymentForm');
		$response = [
			"status" => true,
			"help" => ['splitit_paymentmethod' => [], 'splitit_paymentredirect' => []],
			"isActive" => "",
			"pageType" => "",
			"displayInstallmentPriceOnPage" => "",
			"numOfInstallmentForDisplay" => "",
			"installmetPriceText" => "",
			"grandTotal" => "",
			"currencySymbol" => "",
			"splititLogoSrc" => "",
			"splititLogoBackgroundSrc" => "",
		];

		$isEnable = $this->helper->getConfig("payment/splitit_paymentmethod/enable_installment_price");
		if ($isEnable == "") {
			$isEnable = 0;
		}
		if ($this->helper->getConfig("payment/splitit_paymentmethod/faq_link_enabled")) {
			$response['help']['splitit_paymentmethod']["title"] = $this->helper->getConfig("payment/splitit_paymentmethod/faq_link_title");
			$response['help']['splitit_paymentmethod']["link"] = $this->helper->getConfig("payment/splitit_paymentmethod/faq_link_title_url");
		}
		if ($this->helper->getConfig("payment/splitit_paymentredirect/faq_link_enabled")) {
			$response['help']['splitit_paymentredirect']["title"] = $this->helper->getConfig("payment/splitit_paymentredirect/faq_link_title");
			$response['help']['splitit_paymentredirect']["link"] = $this->helper->getConfig("payment/splitit_paymentredirect/faq_link_title_url");
		}
		$displayInstallmentPriceOnPage = $this->helper->getConfig("payment/splitit_paymentmethod/installment_price_on_pages");
		$numOfInstallmentForDisplay = $this->helper->getConfig("payment/splitit_paymentmethod/installments_count");
		$installmetPriceText = $this->helper->getConfig("payment/splitit_paymentmethod/installment_price_text");
		$SplititLogoSrc = $this->helper->getConfig("payment/splitit_paymentmethod/splitit_logo_src");
		$SplititLogoBackgroundSrc = $this->helper->getConfig("payment/splitit_paymentmethod/splitit_logo__bakcground_href");
		if (is_null($installmetPriceText)) {
			$installmetPriceText = "";
		} else {
			$textArr = explode(' ', $installmetPriceText);
			$changeindex = array_search('SPLITIT', $textArr);
			if ($changeindex) {
				$replace = "<a href='" . $SplititLogoBackgroundSrc . "' target='_blank'><img class='logoWidthSrc' style='height: 24px;display: inline-block;margin-bottom: -8px;' src='" . $SplititLogoSrc . "' alt='SPLITIT'/></a>";
				$textToChange = str_replace('SPLITIT', $replace, $textArr[$changeindex]);
				unset($textArr[$changeindex]);
				$newText = __(implode(' ', $textArr));
				$newTextArr = explode(' ', $newText);
				$newVal = array($changeindex => $textToChange);
				$newList = array_merge(array_slice($newTextArr, 0, $changeindex), $newVal, array_slice($newTextArr, $changeindex));
				$installmetPriceText = implode(' ', $newList);
			}
		}

		if ($SplititLogoSrc && $SplititLogoBackgroundSrc) {
			$response["splititLogoSrc"] = $SplititLogoSrc;
			$response["splititLogoBackgroundSrc"] = $SplititLogoBackgroundSrc;
		}

		$response["isActive"] = $isEnable;
		$response["displayInstallmentPriceOnPage"] = $displayInstallmentPriceOnPage;
		$response["numOfInstallmentForDisplay"] = $numOfInstallmentForDisplay;
		$response["installmetPriceText"] = __($installmetPriceText);

		$cart = $this->_objectManager->get("\Magento\Checkout\Model\Cart");
		$totalAmount = $cart->getQuote()->getGrandTotal();
		$response["grandTotal"] = number_format((float) $totalAmount, 2, '.', '');
		$response["currencySymbol"] = $this->helper->getCurrencyData();

		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		if ($this->paymentForm->checkProductBasedAvailability() || $this->payment->checkProductBasedAvailability()) {
			return $resultJson->setData($response);
		} else {
			return $resultJson->setData(array('status' => false));
		}
		/* echo $data = $this->helper->encodeData($response);
          return; */
	}

}
