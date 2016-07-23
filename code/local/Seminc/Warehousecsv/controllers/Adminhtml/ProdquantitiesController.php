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
        $errors = '';
        $csv = new Varien_File_Csv();
        $data = $csv->getData($filename);
        var_dump( $data );
        //get models
        $_product = Mage::getModel('catalog/product');
        $_warehouse = Mage::getModel('warehousecsv/warehouse');
        $_prodquantities = Mage::getModel('warehousecsv/prodquantities');
        //process all rows in csv
        foreach ($data as $key=>$row) {
            $prodname = $row[0];
            $proddelta = $row[1];
            $warehname = $row[2];
            echo "<br>prodname=".$row[0]." delta=".$row[1]." wareh.=".$row[2];
            //load warehouse model
            $_warehouse = $_warehouse->load($warehname,'warehousename');
            if (!$_warehouse) {
                //create a new warehouse
                $_warehouse->setWarehousename($warehname);
                //TODO: tay/exception required
                $_warehouse->save();
                echo "<br>create warehouse ".$warehname;
            }
            $warehouse_id = $_warehouse->getWarehouse_id();
            //load product model
            $_product = $_product->loadByAttribute('name',$prodname);
            if ($_product){
                //precess only existing products
                //get product SKU
                $product_sku = $_product->getSku();
                echo "<br>get product SKU=".$product_sku;
                //get product inventory quantity
                $_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                $product_qty = $_stock->getQty();
                //load produqntites model
                $_prodquantities_col = $_prodquantities->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse_id)
                    ->addFieldToFilter('product_sku', $product_sku);
                $_prodquantities = $_prodquantities_col->getFirstItem();
                if ($_prodquantities->getId()){
                    //the item exists in warehouse module
                    $new_prodquantities = $_prodquantities->getProdqtperwareh()+ $proddelta;
                    echo "<br>prepare change existing product qty from ".$_prodquantities->getProdqtperwareh()." to ".$new_prodquantities;
                    if ($new_prodquantities < 0) {
                        $new_prodquantities = 0;
                        //TODO: tay/exception required?
                        $_prodquantities->delete();
                        echo "<br>zero - delete existing module product qty record";
                    } else {
                        $_prodquantities->setProdqtperwareh($new_prodquantities);
                        //TODO: tay/exception required
                        $_prodquantities->save();
                        echo "<br>save change existing product qty";
                    }
                }
                else {
                    //the item does not exist in warehouse module - update through mage core inventory
                    $new_prodquantities = $product_qty + $proddelta;
                    echo "<br>prepare create new product qty from ".$product_qty." to ".$new_prodquantities;
                    if ($new_prodquantities < 0) {
                        $new_prodquantities = 0;
                        echo "<br>new qty=0 without existing product qty - do nothing!";
                    } else {
                        $_prodquantities->setProdqtperwareh($new_prodquantities);
                        //TODO: tay/exception required
                        $_prodquantities->save();
                        echo "<br>save change new product qty";
                    }
                }
                //if ($new_prodquantities < 0) { $new_prodquantities = 0; }
                //update mage core inventory
                //$product_qty = $new_prodquantities;
                $_stock->setQty($new_prodquantities);
                $_stock->setData('is_in_stock',$new_prodquantities ? 1 : 0);
                //TODO: tay/exception required
                $_stock->save();
                echo "<br>save change mage inventory update from ".$product_qty." to ".$new_prodquantities;

            } else {
                //exception/log that product does not exist
                $errors = $errors.'"'.$prodname.'" does not exist<br>';
            }

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