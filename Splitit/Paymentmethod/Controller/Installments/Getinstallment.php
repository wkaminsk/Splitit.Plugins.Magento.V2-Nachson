<?php
/**
 * Copyright © 2019 Splitit
 * 
 */
namespace Splitit\Paymentmethod\Controller\Installments;
use Magento\Framework\Controller\ResultFactory;

class Getinstallment extends \Magento\Framework\App\Action\Action {

	protected $helper;
	protected $cart;
	protected $splititSource;
	protected $storeManager;
	protected $currency;
	protected $jsonHelper;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Splitit\Paymentmethod\Helper\Data $helper,
		\Magento\Checkout\Model\Cart $cart,
		\Splitit\Paymentmethod\Model\Source\Installments $splititSource,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Magento\Directory\Model\Currency $currency
	) {
		$this->storeManager = $storeManager;
		$this->currency = $currency;
		$this->helper = $helper;
		$this->cart = $cart;
		$this->splititSource = $splititSource;
		$this->jsonHelper = $jsonHelper;
		parent::__construct($context);
	}

	/**
	 * Get number of installment dropdown
	 * @return Json
	 **/
	public function execute() {
		$response = [
			"status" => true,
			"errorMsg" => "",
			"successMsg" => "",
			"installmentHtml" => "",
			"helpSection" => "",

		];

		$totalAmount = $this->cart->getQuote()->getGrandTotal();

		$selectInstallmentSetup = $this->helper->getConfig('payment/splitit_paymentmethod/select_installment_setup');
		$options = $this->splititSource->toOptionArray();
		$currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
		$currencySymbol = $this->currency->load($currentCurrencyCode)->getCurrencySymbol();

		$installmentHtml = '<option value="">--' . __('No Installment available') . '--</option>';
		$countInstallments = $installmentValue = 0;
		if ($selectInstallmentSetup == "" || $selectInstallmentSetup == "fixed") {
			$installments = $this->helper->getConfig("payment/splitit_paymentmethod/fixed_installment");

			if ($installments) {
				$installmentHtml = '<option value="">--' . __('Please Select') . '--</option>';
				foreach (explode(',', $installments) as $value) {
					$installmentValue = $value;
					$countInstallments++;
					$installmentHtml .= '<option value="' . $value . '">' . $value . ' ' . __('Installments') . '</option>';
				}

			}
		} else {
			$installmentHtml = '<option value="">--' . __('Please Select') . '--</option>';
			$depandingOnCartInstallments = $this->helper->getConfig("payment/splitit_paymentmethod/depanding_on_cart_total_values");
			$depandingOnCartInstallmentsArr = $this->jsonHelper->jsonDecode($depandingOnCartInstallments);
			$dataAsPerCurrency = [];
			foreach ($depandingOnCartInstallmentsArr as $data) {
				$dataAsPerCurrency[$data->doctv->currency][] = $data->doctv;
			}

			if (count($dataAsPerCurrency) && isset($dataAsPerCurrency[$currentCurrencyCode])) {

				foreach ($dataAsPerCurrency[$currentCurrencyCode] as $data) {
					if ($totalAmount >= $data->from && !empty($data->to) && $totalAmount <= $data->to) {
						foreach (explode(',', $data->installments) as $n) {
							$installmentValue = $n;
							$countInstallments++;
							$installmentHtml .= '<option value="' . $n . '">' . $n . ' ' . __('Installments') . '</option>';
						}
						break;
					} else if ($totalAmount >= $data->from && empty($data->to)) {
						foreach (explode(',', $data->installments) as $n) {
							$installmentValue = $n;
							$countInstallments++;
							$installmentHtml .= '<option value="' . $n . '">' . $n . ' ' . __('Installments') . '</option>';
						}
						break;
					}
				}
			}

		}
		$response["installmentHtml"] = $installmentHtml;
		$response["installmentShow"] = true;
		if ($countInstallments == 1 && $installmentValue == 1) {
			$response["installmentShow"] = false;
		}
		$response["helpSection"] = $this->getHelpSection();
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		return $resultJson->setData($response);

	}

	/**
	 * Get help variable frm configurations
	 * @return array
	 **/
	private function getHelpSection() {
		$baseUrl = $this->storeManager->getStore()->getBaseUrl();
		$help = [];

		if ($this->helper->getConfig("payment/splitit_paymentmethod/faq_link_enabled")) {
			$help["title"] = $this->helper->getConfig("payment/splitit_paymentmethod/faq_link_title");
			$help["link"] = $baseUrl . "splititpaymentmethod/help/help";
			$help["link"] = $this->helper->getConfig("payment/splitit_paymentmethod/faq_link_title_url");
		}
		return $help;
	}

}
