<?php

namespace Splitit\Paymentmethod\Model\Quote\Address\Total;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var string
     */
    protected $_code = 'fee';
    /**
     * @var \Splitit\Paymentmethod\Helper\Data
     */
    protected $_helperData;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $_quoteValidator = null;

    /**
     * Fee constructor.
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\Data\PaymentInterface $payment
     * @param \Splitit\Paymentmethod\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\Data\PaymentInterface $payment,
        \Splitit\Paymentmethod\Helper\Data $helperData
    )
    {
        $this->_quoteValidator = $quoteValidator;
        $this->_helperData = $helperData;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Collect totals process.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $fee = 0;
        if($this->_helperData->canApply($quote)) {
//            echo "Fee-Totals";
            $fee = $this->_helperData->getFee($quote);
//            var_dump($fee);
        }
//        $fee=0;
        $total->setFeeAmount($fee);
        $total->setBaseFeeAmount($fee);
        $total->setTotalAmount('fee_amount', $fee);
        $total->setBaseTotalAmount('base_fee_amount', $fee);
        $quote->setFeeAmount($fee);
        $quote->setBaseFeeAmount($fee);
        // echo 'grandTotal='.$total->getGrandTotal().' , fee_amount='.$total->getFeeAmount().' ,';
        // $total->setGrandTotal($total->getGrandTotal() + $total->getFeeAmount());
        // echo 'grandTotal='.$total->getGrandTotal().' ,';
        // echo 'baseGrandTotal='.$total->getBaseGrandTotal().' , base_fee_amount='.$total->getBaseFeeAmount().' ,';
        // $total->setBaseGrandTotal($total->getBaseGrandTotal() + $total->getBaseFeeAmount());
        // echo 'baseGrandTotal='.$total->getBaseGrandTotal();exit;
        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        $result = [
            'code' => $this->getCode(),
            'title' => __('Splitit Fee'),
            'value' => $quote->getFeeAmount()
        ];
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Splitit Fee');
    }
}