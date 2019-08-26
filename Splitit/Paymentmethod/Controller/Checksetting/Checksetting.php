<?php

/**
 * Copyright © 2019 Splitit
 */

namespace Splitit\Paymentmethod\Controller\Checksetting;

use Magento\Framework\Controller\ResultFactory;

class Checksetting extends \Magento\Framework\App\Action\Action {

    /**
     * Splitit Helper
     * @var Splitit\Helper\Data 
     */
    private $helper;

    /**
     * Splitit API model
     * @var Splitit\Paymentmethod\Model\Api 
     */
    private $apiModelObj;

    /**
     * Contructor
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Splitit\Helper\Data $helper
     * @param \Splitit\Paymentmethod\Model\Api $apiModelObj
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Splitit\Helper\Data $helper,
        \Splitit\Paymentmethod\Model\Api $apiModelObj
    ) {
        $this->helper = $helper;
        $this->apiModelObj = $apiModelObj;
        parent::__construct($context);
    }

    /**
     * To check SplitIt Api credentials are correct
     * @return Json
     */
    public function execute() {
        $response = [
            "status" => false,
            "errorMsg" => "",
            "successMsg" => "",
        ];
        $loginResponse = $this->apiModelObj->apiLogin();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$loginResponse["status"]) {
            $response["errorMsg"] = $loginResponse["errorMsg"];
            $resultJson->setData($response);
            return $resultJson;
        }
        if ($this->helper->getConfig("payment/splitit_paymentmethod/sandbox_flag")) {
            $response["successMsg"] = "[Sandbox Mode] Successfully login! API available!";
        } else {
            $response["successMsg"] = "[Production Mode] Successfully login! API available!";
        }
        $response["status"] = true;

        $resultJson->setData($response);
        return $resultJson;
    }

}
