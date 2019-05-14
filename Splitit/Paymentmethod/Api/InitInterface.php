<?php
namespace Splitit\Paymentmethod\Api;
 
interface InitInterface
{
    /**
     * Returns Splitit API response
     *
     * @api
     * @param mixed $data init Params.
     * @return mixed Splitit API reponse
     */
    public function initSplitit($data);
}