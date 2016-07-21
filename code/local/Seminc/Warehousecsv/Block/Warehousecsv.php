<?php
class Seminc_Warehousecsv_Block_Warehousecsv extends Mage_Core_Block_Template
{
    public function testblocfunction()
    {
        return "Hello tuts+ world - we are in the Block mode now";
    }
    
    public function testproductfunction()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('qty')
            ->addAttributeToSelect('store');
        $collection->setOrder('name', 'asc');
        $collection->load();

        return $collection;
    }

    public function prodinfo2blocfunction()
    {
        return "Hello world - we are in the Product page now";
    }
}