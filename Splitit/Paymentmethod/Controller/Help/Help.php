<?php

namespace Splitit\Paymentmethod\Controller\Help;

class Help extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @return object
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    /**
     * Get the Page factory object
     * @return object
     */
    public function execute() {
        return $this->_pageFactory->create();
    }

}
