<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 22.07.2016
 * Time: 15:24
 */
class Seminc_Warehousecsv_Block_Adminhtml_Csvuploadform extends Mage_Adminhtml_Block_Template
{
    //here we can put some logic if we want to call it in our adminblock.phtml
    public function getSubmitUrl()
    {
        //return Mage::getUrl('adminhtml').'prodquantities/csvformsubmit';
        return Mage::helper("adminhtml")->getUrl("*/*/csvformsubmit");
    }
}