<?php

namespace Splitit\Paymentmethod\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\View\Element\Template\Context;

//use \Magento\Framework\Json\Helper\Data;
class Data extends AbstractHelper {

	/**
	 * Total Code
	 */
	const TOTAL_CODE = 'fee_amount';

	/**
	 * Grand Total Code
	 */
	const GRAND_TOTAL_CODE = 'grand_total';

	/**
	 * @var array
	 */
	public $methodFee = NULL;

	public $checkoutSession;
	public $productMetadataInterface;
	public $jsonObject;
	public $storeManager;
	public $currency;
	public $storeLocale;

	public static $selectedIns;

	/**
	 * Constructor
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
		\Magento\Framework\Json\Helper\Data $jsonObject,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Directory\Model\Currency $currency,
		\Magento\Framework\Locale\Resolver $storeLocale
	) {
		$this->checkoutSession = $checkoutSession;
		$this->productMetadataInterface = $productMetadataInterface;
		$this->jsonObject = $jsonObject;
		$this->storeManager = $storeManager;
		$this->currency = $currency;
		$this->storeLocale = $storeLocale;
		parent::__construct($context);
		$this->_getMethodFee();
	}

	/**
	 * To get the config value
	 * @return string
	 */
	public function getConfig($config_path) {
		return $this->scopeConfig->getValue(
			$config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * To json encode
	 * @return Json
	 */
	public function encodeData($dataToEncode) {

		$encodedData = $this->jsonObject->jsonEncode($dataToEncode);
		return $encodedData;
	}

	/**
	 * To get the currency symbol
	 * @return string
	 */
	public function getCurrencyData() {
		$currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
		$currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();

		$currency = $this->currency->load($currencyCode);
		return $currencySymbol = $currency->getCurrencySymbol();
	}

	/**
	 * To get Splitit culture names
	 * @return string
	 */
	public function getCultureName($paymentForm = false) {
		$storelang = $this->storeLocale->getLocale();
		$splititSupportedCultures = $this->getSplititSupportedCultures();

		if (count($splititSupportedCultures) && in_array(str_replace('_', '-', $storelang), $splititSupportedCultures)) {
			return str_replace('_', '-', $storelang);
		} else {
			if ($paymentForm) {
				return $this->getConfig('payment/splitit_paymentredirect/splitit_fallback_language');
			}

			return $this->getConfig('payment/splitit_paymentmethod/splitit_fallback_language');
		}
	}

	/**
	 * To get Splitit supported culture names
	 * @return array
	 */
	public function getSplititSupportedCultures() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api = $objectManager->get('Splitit\Paymentmethod\Model\Api');
		$apiUrl = $api->getApiUrl();
		$getSplititSupportedCultures = $api->getSplititSupportedCultures($apiUrl . "api/Infrastructure/SupportedCultures");

		$decodedResult = json_decode($getSplititSupportedCultures, true);
		if (isset($decodedResult["ResponseHeader"]["Succeeded"]) && $decodedResult["ResponseHeader"]["Succeeded"] == 1 && count($decodedResult["SupportedCultures"])) {
			return $decodedResult["SupportedCultures"];
		}
		return array();
	}

	/**
	 * Retrieve Payment Method Fees from Store Config
	 * @return array
	 */
	protected function _getMethodFee() {

		if (is_null($this->methodFee)) {
			$this->methodFee['splitit_paymentmethod'] = array(
				'fee' => $this->getConfig('payment/splitit_paymentmethod/splitit_fees'),
			);
			$this->methodFee['splitit_paymentredirect'] = array(
				'fee' => $this->getConfig('payment/splitit_paymentredirect/splitit_fees'),
			);
		}
		return $this->methodFee;
	}

	/**
	 * Check if Extension is Enabled config
	 * @return bool
	 */
	public function isEnabled($method = '') {
		if ($method) {
			return $this->getConfig("payment/$method/splitit_fee_on_total");
		}
		return $this->getConfig('payment/splitit_paymentmethod/splitit_fee_on_total');
	}

	/**
	 * @param \Magento\Quote\Model\Quote $quote
	 * @return bool
	 */
	public function canApply(\Magento\Quote\Model\Quote $quote) {

		/** check module or config **/
		if ($method = $quote->getPayment()->getMethod()) {
			if ($this->isEnabled($method)) {
				if (isset($this->methodFee[$method])) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param \Magento\Quote\Model\Quote $quote
	 * @return float|int
	 */
	public function getFee(\Magento\Quote\Model\Quote $quote) {
		$method = $quote->getPayment()->getMethod();
		$fee = $this->methodFee[$method]['fee'];

		if ($method == 'splitit_paymentmethod') {
			$fee = 0;
			if (version_compare($this->getMagentoVersion(), '2.3.0', '<')) {

				$feeTable = @unserialize($this->getConfig("payment/$method/splitit_fee_table", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
				if ($feeTable == false) {
					$feeTable = json_decode($this->getConfig("payment/$method/splitit_fee_table", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), true);
				}
			} else {

				$feeTable = json_decode($this->getConfig("payment/$method/splitit_fee_table", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), true);
			}
			$selectedInstallment = $this->checkoutSession->getSelectedIns();

			if ($selectedInstallment) {
				foreach ($feeTable as $value) {
					if ($value['noi'] == $selectedInstallment) {
						$fixedFee = $value['fixed'];
						$percentFee = $value['percent'];
						$totals = $quote->getTotals();
						$sum = 0;
						foreach ($totals as $total) {
							if (($total->getCode() != self::TOTAL_CODE) && ($total->getCode() != self::GRAND_TOTAL_CODE)) {
								$sum += (float) $total->getValue();
							}
							if (($total->getCode() == 'shipping') && ($total->getValue() == 0)) {
								$sum += (float) $quote->getShippingAddress()->getShippingAmount();
							}
						}

						return ($sum * ($percentFee / 100)) + $fixedFee;
					}
				}
			}
			return $fee;
		} else {

			$feeType = $this->getFeeType($method);
			if ($feeType == \Splitit\Paymentmethod\Model\Source\Feetypes::FIXED) {
				return $fee;
			} else {
				$totals = $quote->getTotals();
				$sum = 0;
				foreach ($totals as $total) {
					if (($total->getCode() != self::TOTAL_CODE) && ($total->getCode() != self::GRAND_TOTAL_CODE)) {
						$sum += (float) $total->getValue();
					}
					if (($total->getCode() == 'shipping') && ($total->getValue() == 0)) {
						$sum += (float) $quote->getShippingAddress()->getShippingAmount();
					}
				}

				return ($sum * ($fee / 100));
			}
		}
	}

	/**
	 * Retrieve Fee type from Store config (Percent or Fixed)
	 * @return string
	 */
	public function getFeeType($method = '') {
		if ($method) {
			return $this->getConfig("payment/$method/splitit_fee_types");
		}
		return $this->getConfig('payment/splitit_paymentmethod/splitit_fee_types');
	}

	/**
	 * Retrieve Current Magento Version
	 * @return string
	 */
	public function getMagentoVersion() {
		return $this->productMetadataInterface->getVersion();
	}

	/**
	 * Retrieve Installment price text
	 * @return string
	 */
	public function getInstallmentPriceText() {
		$text = [];

		if ($this->getConfig("payment/splitit_paymentredirect/active") && $this->getConfig("payment/splitit_paymentredirect/enable_installment_price")) {

			$text['price_text'] = 'or {NOI} interest-free payments of {AMOUNT} with SPLITIT';
			$text['logo_src'] = $this->getConfig("payment/splitit_paymentredirect/splitit_logo_src");
			$text['bakcground_href'] = $this->getConfig("payment/splitit_paymentredirect/splitit_logo__bakcground_href");
			$text['installments_count'] = $this->getConfig("payment/splitit_paymentredirect/installments_count");
			$text['installment_price_on_pages'] = $this->getConfig("payment/splitit_paymentredirect/installment_price_on_pages");
			$text['help_link'] = $this->getConfig("payment/splitit_paymentredirect/faq_link_title_url");
			$text['help_title'] = __('Learn More');
		}

		if ($this->getConfig("payment/splitit_paymentmethod/active") && $this->getConfig("payment/splitit_paymentmethod/enable_installment_price")) {
			$text['price_text'] = 'or {NOI} interest-free payments of {AMOUNT} with SPLITIT';
			$text['logo_src'] = $this->getConfig("payment/splitit_paymentmethod/splitit_logo_src");
			$text['bakcground_href'] = $this->getConfig("payment/splitit_paymentmethod/splitit_logo__bakcground_href");
			$text['installments_count'] = $this->getConfig("payment/splitit_paymentmethod/installments_count");
			$text['installment_price_on_pages'] = $this->getConfig("payment/splitit_paymentmethod/installment_price_on_pages");
			$text['help_link'] = $this->getConfig("payment/splitit_paymentmethod/faq_link_title_url");
			$text['help_title'] = __('Learn More');
		}
		return $text;
	}

}
