<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 01.07.2016
 * Time: 17:13
 */
class Seminc_Warehousecsv_Adminhtml_ProdquantitiesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
//        $this->loadLayout();
//        $this->renderLayout();
//        return $this;
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }

    public function csvformsubmitAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
                try
                {
                    $path = Mage::getBaseDir().DS.'media'.DS.'import'.DS.'csv'.DS;  //desitnation directory
                    $fname = $_FILES['file_csv']['name']; //file name
                    $fullname = $path.$fname;
                    $uploader = new Varien_File_Uploader('file_csv'); //load class
                    $uploader->setAllowedExtensions(array('CSV','csv')); //Allowed extension for file
                    $uploader->setAllowCreateFolders(true); //for creating the directory if not exists
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $uploader->save($path, $fname); //save the
                }
                catch (Exception $e)
                {
                    $uploadError = $fname." - Upload error or invalid file format";
                }
            } else {
                $uploadError = "Upload file does not specified";
            }
        }
        if ($uploadError) {
            Mage::getSingleton('adminhtml/session')->addError( Mage::helper('core')->__($uploadError));
            //$this->_redirect('*/*/');
            $this->_redirectReferer();
            return;
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess( Mage::helper('core')->__($fname." - File upload success"));

            $this->_csvfileprocessingAction($fullname);

            $this->_redirectReferer();
            return;
        }
    }

    protected function _csvfileprocessingAction($filename)
    {
        $csv = new Varien_File_Csv();
        $data = $csv->getData($filename);
        var_dump( $data );

        foreach ($data as $key=>$row) {
            $prodname = $row[0];
            $proddelta = $row[1];
            $warehname = $row[2];
            echo "<br>prodname=".$row[0]." delta=".$row[1]." wareh.=".$row[2];
        }

        die();
    }

    /**
     * Initialize action
     * Here, we set the breadcrumbs and the active menu
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('seminc_warehousecsv')
            ->_title($this->__('Manage Products by Warehouses'))
            ->_addBreadcrumb($this->__('Warehouse Management'), $this->__('Manage Products by Warehouses'));
        //add form block BELOW grid
       // $this->loadLayout()->_addContent(
       //     $this->getLayout()
       //         ->createBlock('warehousecsv/adminhtml_csvuploadform')
       //         ->setTemplate('warehousecsv/csvuploadform.phtml'));
        return $this;
    }

    /**
     * Check currently called action by permissions for current user
     * @return bool
     */
    protected function _isAllowed()
    {
        //echo 'is allowed: '.Mage::getSingleton('admin/session')->isAllowed('seminc_testmod_prodmessage');
        return Mage::getSingleton('admin/session')->isAllowed('seminc_warehousecsv_prodquantities');
    }
}