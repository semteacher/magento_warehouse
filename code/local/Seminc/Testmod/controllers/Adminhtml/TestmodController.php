<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 01.07.2016
 * Time: 17:13
 */
class Seminc_Testmod_Adminhtml_TestmodController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }
}