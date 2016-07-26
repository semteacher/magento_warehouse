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

    /**
     * @param $attrCode                 (the attribute code, for example 'name' or 'sku')
     * @param $entityType               (catalog_product, catalog_category, customer, customer_address)
     * @param $joinField                (the field to join the attribute value entity_id on (for example main_table.product_id))
     * @param $collection               (the collection to join to)
     * @param int $storeID              (store ID to filter the attribute values by store)
     * @return $this
     */
    public function joinEAV($attrCode, $entityType, $joinField, $collection, $storeID=0)
    {
        $attr = $this->getCachedAttr($entityType, $attrCode);
        $attrID = $attr->getAttributeId();
        $attrTable = $attr->getBackendTable();
        if ($attr->getBackendType() == 'static') {
            $joinSql = "{$attrTable}.entity_id={$joinField}";
            //don't use an alias for static table, use table name
            $alias = $attrTable;
            //if static join all fields
            $fields = '*';
            //create alias for current field to add as expression attribute
            $fieldAlias = $entityType . '_' . $attrCode;
        }else{
            //for regular attribute, create alias for table (table might be joined multiple times)
            $alias = $entityType . '_' . $attrCode;
            $dbRow = 'value';
            $joinSql = "{$alias}.entity_id={$joinField} AND {$alias}.store_id={$storeID} AND {$alias}.attribute_id={$attrID}";
            //if non-static, create alias for value field in join
            $fields = array($alias => "{$alias}.{$dbRow}");
        }
        //if field or static table is already joined, don't join again
        if (stristr($collection->getSelectSql(), "`{$alias}`")) {
            $dontJoin = true;
        }
        //if field is static, create field alias for display in grid / collection
        if ($attr->getBackendType() == 'static') {
            $collection->addExpressionFieldToSelect($fieldAlias,"{$attrTable}.{$attrCode}");
        }
        if ($dontJoin) {
            return $this;
        }

        //join select
        $collection
            ->getSelect()
            ->joinLeft(
                array($alias => $attrTable),
                $joinSql,
                $fields
            );
        //var_dump($collection);
        return $this;
    }
    /**
     * Get index for grid attribute
     *
     * @param $attrCode
     * @param $entityType
     * @return string
     */
    public function getAttrIndex($attrCode, $entityType){
        return ($entityType . '_' . $attrCode);
    }
    /**
     * Get Filter Index for grid attribute
     *
     * @param $attrCode
     * @param $entityType
     * @return string
     */
    public function getAttrFilterIndex($attrCode, $entityType)
    {
        $attr = $this->getCachedAttr($entityType, $attrCode);
        if ($attr->getBackendType() == 'static') {
            //if static use default entity table
            $index = $attr->getBackendTable() . '.' . $attr->getAttributeCode();
        }else{
            //if non-static use generated table alias value field
            $attrCode = $attr->getAttributeCode();
            $index = "{$entityType}_{$attrCode}.value";
        }
        return $index;
    }
    /**
     * Returns cached attribute if available, else loads attribute
     *
     * @param $entityType
     * @param $attrCode
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getCachedAttr($entityType,$attrCode)
    {
        //retrieve the attribute object
        if ($cachedAttr = Mage::registry("eavjoin_{$entityType}_{$attrCode}")) {
            $attr = $cachedAttr;
        }else{
            $attr = Mage::getModel("eav/config")->getAttribute($entityType, $attrCode);
            Mage::register("eavjoin_{$entityType}_{$attrCode}",$attr);
        }
        return $attr;
    }
}