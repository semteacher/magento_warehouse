<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 9:58
 */
class Seminc_Warehousecsv_Model_Resource_Prodquantities_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('warehousecsv/prodquantities');
    }
    
    public function addProductName()
    {
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
        $prodNameAttrId = Mage::getModel('eav/entity_attribute')
            ->loadByCode($entityTypeId, 'name')
            ->getAttributeId();
        $this->getSelect()
            ->joinLeft(
                array('prod' => 'catalog_product_entity'),
                'prod.sku = main_table.product_sku',
                array('entity_id')
            )
            ->joinLeft(
                array('cpev' => 'catalog_product_entity_varchar'),
                'cpev.entity_id=prod.entity_id AND cpev.attribute_id=' . $prodNameAttrId . '',
                array('product_name' => 'value')
            );

        return $this;
    }

    public function addWarehouseName()
    {
        $this->getSelect()
            ->joinLeft(
                array('wareh' => 'csv_warehouse_data'),
                'wareh.warehouse_id = main_table.warehouse_id',
                array('warehousename')
            );
    }
}