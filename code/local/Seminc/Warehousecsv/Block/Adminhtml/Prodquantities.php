<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 20:04
 */
class Seminc_Warehousecsv_Block_Adminhtml_Prodquantities extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'seminc_warehousecsv_adminhtml';
        $this->_controller = 'prodquantities';
        $this->_headerText = $this->__('Manage Products by Warehouses');
        
        parent::__construct();

        $this->_removeButton('add');
    }
}