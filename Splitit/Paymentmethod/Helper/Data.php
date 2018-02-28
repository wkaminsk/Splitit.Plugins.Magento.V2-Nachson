<?php

namespace Splitit\Paymentmethod\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Directory\Model\Currency;

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

    /**
     * Constructor
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->_getMethodFee();
    }

    public function getConfig($config_path) {
        return $this->scopeConfig->getValue(
                        $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function encodeData($dataToEncode) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $jsonObject = $objectManager->create('\Magento\Framework\Json\Helper\Data');
        $encodedData = $jsonObject->jsonEncode($dataToEncode);
        return $encodedData;
    }

    public function getCurrencyData() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $currencyRate = $storeManager->getStore()->getCurrentCurrencyRate();

        $currency = $objectManager->create('Magento\Directory\Model\Currency')->load($currencyCode);
        return $currencySymbol = $currency->getCurrencySymbol();
    }

    /**
     * Retrieve Payment Method Fees from Store Config
     * @return array
     */
    protected function _getMethodFee() {

        if (is_null($this->methodFee)) {
            $this->methodFee['splitit_paymentmethod'] = array(
                'fee' => $this->getConfig('payment/splitit_paymentmethod/splitit_fees')
            );
            $this->methodFee['splitit_paymentredirect'] = array(
                'fee' => $this->getConfig('payment/splitit_paymentredirect/splitit_fees')
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

        /*         * @TODO check module or config* */
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
//        $fee = $this->getConfig('payment/splitit_paymentmethod/splitit_fees');
        $feeType = $this->getFeeType($method);
        if ($feeType == \Splitit\Paymentmethod\Model\Source\Feetypes::FIXED) {
            return $fee;
        } else {
            $totals = $quote->getTotals();
            $sum = 0;
            foreach ($totals as $total) {
                if (($total->getCode() != self::TOTAL_CODE) && ($total->getCode() != self::GRAND_TOTAL_CODE)) {
                    $sum += (float) $total->getValue();
                    // echo $total->getCode().'='.((float) $total->getValue()).' , ';
                }
                if (($total->getCode() == 'shipping') && ($total->getValue() == 0)) {
                    $sum += (float) $quote->getShippingAddress()->getShippingAmount();
                    // echo $total->getCode().'='.((float) $quote->getShippingAddress()->getShippingAmount()).' , ';
                }
            }
            // echo 'sum='.$sum.' , ';
            // echo 'grandTotal='.$quote->getGrandTotal().' , ';
            // echo 'fee='.$fee.' , ';
            // echo 'new_fee='.($sum * ($fee / 100));
            // exit;
            return ($sum * ($fee / 100));
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

}
