<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 9:39
 */
class Seminc_Warehousecsv_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get current product total qty in all warehouses
     * @product_sku (str) product_sku
     * @return (int) product total qty in all warehouses
     */
    public function getWarehouseProductTotalQty($product_sku) 
    {
        $prodquantitiesCollection = Mage::getModel('warehousecsv/prodquantities')->getCollection()
            ->addExpressionFieldToSelect('prod_total_qty', 'SUM(prodqtperwareh)', array('prodqtperwareh'))
            ->addFieldToFilter('product_sku', $product_sku);
        //$collection->getSelect()->group('product_sku');
        $prodquantitiesTotal = $prodquantitiesCollection->getFirstItem()->getData('prod_total_qty');
        return $prodquantitiesTotal;
    }
    
    /**
     * Get produqntites resource model
     * @product_sku (str) product_sku
     * @return (int) product total qty in all warehouses
     */
    public function getWarehouseProductQtyResModel($warehouseId, $productSku)
    {
        //load produqntites resource model collection
        $prodquantitiesCollection = Mage::getModel('warehousecsv/prodquantities')->getCollection()
            ->addFieldToFilter('warehouse_id', $warehouseId)
            ->addFieldToFilter('product_sku', $productSku);
        $prodQtyResModel = $prodquantitiesCollection->getFirstItem();
        return $prodQtyResModel;
    }
    
}