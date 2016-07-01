<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 06.06.2016
 * Time: 20:22
 */
class Seminc_Testmod_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //echo "Hello tuts+ World -sem module";
        $this->loadLayout();
        $this->renderLayout();
    }
    public function testAction()
    {
        echo "test 2 - action";
    }
}