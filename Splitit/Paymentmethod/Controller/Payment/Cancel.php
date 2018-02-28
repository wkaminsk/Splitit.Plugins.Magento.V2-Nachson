<?php

namespace Splitit\Paymentmethod\Controller\Payment;

class Cancel extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Splitit\Paymentmethod\Helper\Data
     */
    protected $_helperData;
    /** 
     * @var \Magento\Sales\Api\Data\OrderInterface $order 
     */
    protected $order;
    protected $quoteFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Sales\Api\Data\OrderInterface $order,
    \Magento\Quote\Model\QuoteFactory $quoteFactory,
    \Splitit\Paymentmethod\Helper\Data $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData = $helperData;
        $this->order = $order;
        $this->quoteFactory = $quoteFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session'); 
        parent::__construct($context);
    }
    
    public function execute() {
//        die("cancel controller");
        $session = $this->checkoutSession;
        $session->setQuoteId($session->getSplititQuoteId());
        
        if ($session->getLastRealOrderId()) {
            $order = $this->order->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            $this->checkoutSession->restoreQuote();;
            $order = $session->getLastRealOrder();
            $quote = $this->quoteFactory->create()->load($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $session
                    ->replaceQuote($quote)
                    ->unsLastRealOrderId();
                
            }
        }    
        $this->_redirect("checkout/cart")->sendResponse();
    }

}
