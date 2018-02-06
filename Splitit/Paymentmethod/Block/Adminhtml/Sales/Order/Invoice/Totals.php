<?php

namespace Splitit\Paymentmethod\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template {

    protected $_config;
    protected $_order;
    protected $_source;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Tax\Model\Config $taxConfig, array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    public function displayFullSummary() {
        return true;
    }
    
    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    public function getStore() {
        return $this->_order->getStore();
    }

    public function getOrder() {
        return $this->_order;
    }

    public function getLabelProperties() {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties() {
        return $this->getParentBlock()->getValueProperties();
    }

    public function initTotals() {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();
        // echo $this->_order->getFeeAmount();exit;
        $fee = new \Magento\Framework\DataObject(
                [
            'code' => 'fee',
            'strong' => false,
            'value' => $this->_order->getFeeAmount(),
            'base_value' => $this->_order->getFeeAmount(),
            'label' => __('Splitit Fee'),
                ]
        );
        $parent->addTotal($fee, 'fee');
        return $this;
    }

}
