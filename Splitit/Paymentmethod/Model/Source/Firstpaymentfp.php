<?php

namespace Splitit\Paymentmethod\Model\Source;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Option\ArrayInterface; 

class Firstpaymentfp implements ArrayInterface 
{
    /**
     * @return string[]
     */
    public function toOptionArray()
	{
		return array(
            array('value' => 'equal', 'label' => __('Equal to Monthly Payment')),
            array('value' => 'shipping', 'label' => __('Only Shipping')),
            array('value' => 'percentage', 'label' => __('Equal to percentage of the order [X]')),
        );
	}
}
