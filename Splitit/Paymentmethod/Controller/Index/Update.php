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

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Splitit\Paymentmethod\Helper\Data $helperData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Splitit\Paymentmethod\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helperData = $helperData;
        $this->priceHelper = $priceHelper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * To calculate Splitit fees on checkout
     * @return Json
     */
    public function execute() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $quote = $this->checkoutSession->getQuote();
        $method = $quote->getPayment()->getMethod();
        $applyFees = $this->scopeConfig->getValue("payment/$method/splitit_fee_on_total", $storeScope);
        if ($applyFees) {
            try {
                $feeType = $this->scopeConfig->getValue("payment/$method/splitit_fee_types", $storeScope);
                $fees = $this->scopeConfig->getValue("payment/$method/splitit_fees", $storeScope);
                $post = $this->getRequest()->getPostValue();
                $this->checkoutSession->setSelectedIns($this->getRequest()->getParam('selectedIns'));

                $fees = $this->_helperData->getFee($quote);

                $result = $this->resultJsonFactory->create();
                $formattedFees = $this->priceHelper->currency($fees, true, false);
                return $result->setData(['success' => true, 'data' => array('splitit_fees' => $formattedFees)]);
            } catch (\Exception $e) {
                $result = $this->resultJsonFactory->create();
                return $result->setData(['success' => false, 'data' => false]);
            }
        } else {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['success' => FALSE, 'data' => false]);
        }
    }

}
