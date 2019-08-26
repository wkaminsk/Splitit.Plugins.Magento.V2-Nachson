<?php

/**
 * Copyright © 2019 Splitit
 */

namespace Splitit\Paymentmethod\Controller\Getcurrency;

use Magento\Framework\Controller\ResultFactory;

class Getcurrency extends \Magento\Framework\App\Action\Action {

    protected $storeManager;
    protected $currency;
    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->currency = $currency;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Retrieve the list of available currency
     * @return json
     */
    public function execute() {

        $request = $this->request->getParams();
        $response = [
            "status" => false,
            "errorMsg" => "",
            "successMsg" => "",
            "currencySymbol" => "",
            "currencyCode" => ""
        ];
        $currencySymbol = $this->currency->getCurrencySymbol();
        $currencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $response["currencySymbol"] = $currencySymbol;
        $response["currencyCode"] = $currencyCode;
        $response["currencyCodeSymbol"] = $this->getAllavailableCurrencies();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * Retrieve the list of available currency
     * @return array
     */
    private function getAllavailableCurrencies() {
        $allAvailableCurrencyCodes = $this->storeManager->getStore()->getAvailableCurrencyCodes(false);
        $currencyCodeSymbol = [];
        foreach ($allAvailableCurrencyCodes as $value) {
            $currencyCodeSymbol[$value] = $this->currency->load($value)->getCurrencySymbol();
        }
        return $currencyCodeSymbol;
    }

}
