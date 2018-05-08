<?php

namespace Splitit\Paymentmethod\Model\Source;


class Productskus
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productStatus = $objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status');
        $productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');
        $collection = $productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSort('name');
        $collection->addAttributeToFilter('status', ['in' => $productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($productVisibility->getVisibleInSiteIds());
//        $collection->setPageSize(3);
        $skus=array();
        foreach ($collection as $product) {
//            echo "<br/>";
//            print_r($product->getData());
            $skus[]=array('value'=>$product->getId(), 'label' => __($product->getName().' - '.$product->getSku()));            
        }
//        exit;
//        array_multisort($skus, SORT_ASC);
        return $skus;

    }
}