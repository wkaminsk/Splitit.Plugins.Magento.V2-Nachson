<?php

namespace Splitit\Paymentmethod\Model\Source;


class Feetypes
{
    const PERCENTAGE=1;
    const FIXED=2;

    public function toOptionArray()
    {
        return array(
            array('value' => self::PERCENTAGE, 'label' => __('Percentage from Total Amount')),
            array('value' => self::FIXED, 'label' => __('FIXED')),
        );

    }
}