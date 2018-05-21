<?php

namespace Splitit\Paymentmethod\Controller\Index;

class Update extends \Magento\Framework\App\Action\Action {

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

    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Splitit\Paymentmethod\Helper\Data $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData = $helperData;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session'); 
        parent::__construct($context);
    }

    public function execute() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $quote = $this->checkoutSession->getQuote();
        $method=$quote->getPayment()->getMethod();
        $applyFees = $this->scopeConfig->getValue("payment/$method/splitit_fee_on_total", $storeScope);
        if ($applyFees) {
            try {
                $feeType = $this->scopeConfig->getValue("payment/$method/splitit_fee_types", $storeScope);
                $fees = $this->scopeConfig->getValue("payment/$method/splitit_fees", $storeScope);
                $post = $this->getRequest()->getPostValue();
                $this->checkoutSession->setSelectedIns($this->getRequest()->getParam('selectedIns'));
                // $grand_total = $quote->getGrandTotal();
                // if (\Splitit\Paymentmethod\Model\Source\Feetypes::PERCENTAGE == $feeType) {
                //     $fees = ($grand_total * $fees / 100);
                // }
                $fees = $this->_helperData->getFee($quote);
               // $new_grand_total = $grand_total + $fees;
               // $quote->setGrandTotal($new_grand_total);
               // $quote->save();

               // $this->checkoutSession->getQuote()->collectTotals()->save();

                $result = $this->resultJsonFactory->create();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
                $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
                $formattedFees = $priceHelper->currency($fees, true, false);
                return $result->setData(['success' => true, 'data' => array('splitit_fees' => $formattedFees)]);
            } catch (Exception $e) {
                $result = $this->resultJsonFactory->create();
                return $result->setData(['success' => false, 'data' => false]);
            }
        } else {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['success' => FALSE, 'data' => false]);
        }
    }

}
