<?php

namespace Splitit\Paymentmethod\Model\Source;


class Installments
{
    public function toOptionArray()
    {
        return array(
            array('value' => '2', 'label' => __('2 Installments')),
            array('value' => '3', 'label' => __('3 Installments')),
            array('value' => '4', 'label' => __('4 Installments')),
            array('value' => '5', 'label' => __('5 Installments')),
            array('value' => '6', 'label' => __('6 Installments')),
            array('value' => '7', 'label' => __('7 Installments')),
            array('value' => '8', 'label' => __('8 Installments')),
            array('value' => '9', 'label' => __('9 Installments')),
            array('value' => '10', 'label' => __('10 Installments')),
            array('value' => '11', 'label' => __('11 Installments')),
            array('value' => '12', 'label' => __('12 Installments')),
        );

    }
}