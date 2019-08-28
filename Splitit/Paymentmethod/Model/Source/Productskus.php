<?php

namespace Splitit\Paymentmethod\Model\Source;

class Productskus {
	public $skus;
	/**
	 * Get options for product list
	 *
	 * @return array
	 */
	public function toOptionArray($params) {
		$this->skus = array();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productStatus = $objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
		$productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');
		$collection = $productCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		$collection->addAttributeToSort('name');
		$collection->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()]);
		if (isset($params['term']) && $params['term']) {
			$collection->addAttributeToFilter(array(
				array('attribute' => 'name', 'like' => '%' . $params['term'] . '%'),
				array('attribute' => 'sku', 'like' => '%' . $params['term'] . '%'),
			));
		}
		if (isset($params['prodIds']) && $params['prodIds']) {
			$collection->addAttributeToFilter('entity_id', ['in' => $params['prodIds']]);
		}
		$collection->setVisibility($productVisibility->getVisibleInSiteIds());
		$iterator = $objectManager->get('\Magento\Framework\Model\ResourceModel\Iterator');

		$iterator->walk($collection->getSelect(), array(array($this, 'callBackProd')));
		return $this->skus;

	}

	public function callBackProd($args) {
		$this->skus[] = array('value' => $args['row']['entity_id'], 'label' => __($args['row']['name'] . '-' . $args['row']['sku']));
	}

}