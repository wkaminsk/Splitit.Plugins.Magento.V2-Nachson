<?php

namespace Splitit\Paymentmethod\Observer;

// use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * AddFeeToOrderObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Set fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get('Psr\Log\LoggerInterface');
        $logger->debug('fee_amount=0 , base_fee_amount=0');
        
        $quote = $observer->getEvent()->getQuote();
        $feeAmount = $quote->getFeeAmount();
        
        $logger->debug($quote->getId());
        $logger->debug('feeAmount='.$feeAmount);
        
        
        $baseFeeAmount = $quote->getBaseFeeAmount();
        if(!$feeAmount || !$baseFeeAmount) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getEvent()->getOrder();
        $order->setData('fee_amount', $feeAmount);
        $order->setData('base_fee_amount', $baseFeeAmount);

        $logger->debug('fee_amount='.$feeAmount.', base_fee_amount='.$baseFeeAmount);

        return $this;
    }
}