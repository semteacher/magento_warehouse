<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 22.07.2016
 * Time: 15:24
 */
class Seminc_Warehousecsv_Block_Adminhtml_Csvuploadform extends Mage_Adminhtml_Block_Template{
    public function __construct()
    {
        //TODO: does not work
        $this->_headerText = $this->__('Manage Products by Warehouses000');
        parent::__construct();
    }
    //here we can put some logic if we want to call it in our adminblock.phtml
}