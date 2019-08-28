<?php

namespace Splitit\Paymentmethod\Model\Order\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal {
	/**
	 * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
	 * @return $this
	 */
	public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo) {
		$order = $creditmemo->getOrder();
		$orderItems = $order->getAllItems();
		$r = $c = $o = 0;
		foreach ($orderItems as $oitem) {
			$o += $oitem->getQtyOrdered();
			$r += $oitem->getQtyRefunded();
		}
		$creditmemoItems = $creditmemo->getAllItems();
		foreach ($creditmemoItems as $citems) {
			$c += $citems->getQty();
		}
		if ($c == ($o - $r)) {
			if ($order->getFeeAmountInvoiced() > 0) {
				$feeAmountLeft = $order->getFeeAmountInvoiced() - $order->getFeeAmountRefunded();
				$basefeeAmountLeft = $order->getBaseFeeAmountInvoiced() - $order->getBaseFeeAmountRefunded();
				if ($basefeeAmountLeft > 0) {
					$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
					$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
					$creditmemo->setFeeAmount($feeAmountLeft);
					$creditmemo->setBaseFeeAmount($basefeeAmountLeft);
					$order->setFeeAmountRefunded($feeAmountLeft);
					$order->setBaseFeeAmountRefunded($basefeeAmountLeft);
				}
			} else {
				$feeAmount = $order->getFeeAmount();
				$basefeeAmount = $order->getBaseFeeAmount();
				$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmount);
				$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmount);
				$creditmemo->setFeeAmount($feeAmount);
				$creditmemo->setBaseFeeAmount($basefeeAmount);
				$order->setFeeAmountRefunded($feeAmount);
				$order->setBaseFeeAmountRefunded($basefeeAmount);
			}
		}
		return $this;
	}
}