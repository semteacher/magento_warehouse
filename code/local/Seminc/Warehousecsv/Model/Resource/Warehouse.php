<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 9:52
 */
class Seminc_Warehousecsv_Model_Resource_Warehouse extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('warehousecsv/warehouse', 'warehouse_id');
    }
}