<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 9:58
 */
class Seminc_Warehousecsv_Model_Resource_Warehouse_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('warehousecsv/warehouse');
    }
}