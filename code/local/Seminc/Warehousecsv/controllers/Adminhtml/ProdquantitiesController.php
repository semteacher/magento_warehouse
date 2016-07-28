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
            $errors = $this->_csvfileprocessingAction($fullname);
            if ($errors == ''){
                Mage::getSingleton('adminhtml/session')->addSuccess( Mage::helper('core')->__($fname." - File loaded and parsed successfully"));                
            } else {
                Mage::getSingleton('adminhtml/session')->addError( Mage::helper('core')->__($errors));
            }
            $this->_redirectReferer();
            return;
        }
    }
    
    protected function _csvfileprocessingAction($filename)
    {
        $errors = '';
        $csv = new Varien_File_Csv();
        $data = $csv->getData($filename);
        //start transaction
        try 
        {
            $transaction = Mage::getModel('core/resource_transaction');
            //process all rows in csv
        foreach ($data as $key=>$row) {
            $productSku = $row[0];
            $proddelta = (int) $row[1];
            $warehname = $row[2];
            //load warehouse model
            $_warehouse = Mage::getModel('warehousecsv/warehouse')->load($warehname,'warehousename');
            if (!($_warehouse->getId())) {
                //create a new warehouse
                $transaction->addObject($_warehouse);
                $_warehouse->setWarehousename($warehname)->save();
                //TODO: is it tray/exception required?
            }
            $warehouseId = $_warehouse->getId();
            //load product model
            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productSku);
            //process only existing product object
            if (is_object($_product)){
                //get product inventory quantity
                $_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                $productStockQty = $_stock->getQty();
                //get current product total qty in all warehouses
                $prodQtyTotal = Mage::helper('seminc_warehousecsv/data')->getWarehouseProductTotalQty($productSku);
                //used for the first time only, really:
                //При первом запуске подразумевается, что ни на одном из складов нет ни одного продукта.
                //and later to discard qty changes which was made througth product management 
                if ($prodQtyTotal <> $productStockQty) {
                    $productStockQty = $prodQtyTotal;
                }
                //load produqntites model
                $prodQtyResModel = Mage::helper('seminc_warehousecsv/data')->getWarehouseProductQtyResModel($warehouseId, $productSku);
                if ($prodQtyResModel->getId()){
                    //the item exists in warehouse module
                    $newWarehProdQty = $prodQtyResModel->getProdqtperwareh()+ $proddelta;
                    if ($newWarehProdQty <= 0) {
                        $newWarehProdQty = Null;
                        //update inventory qty
                        $productStockQty = $productStockQty - $prodQtyResModel->getProdqtperwareh();
                        //TODO: is it tray/exception required?
                        $transaction->addObject($prodQtyResModel);
                        $prodQtyResModel->delete();
                    } else {
                        //update inventory qty
                        $productStockQty = $productStockQty + $proddelta;
                        //save warehouse qty
                        $transaction->addObject($prodQtyResModel);
                        $prodQtyResModel->addData(array('product_sku'=>$productSku, 'warehouse_id'=>$warehouseId, 'prodqtperwareh'=>$newWarehProdQty))->save();
                        //TODO: is it tray/exception required
                        //$prodQtyResModel->save();
                    }
                }
                else {
                    //the item does not exist in warehouse module - update through mage core inventory
                    if ($proddelta > 0) {
                        //we can just ADD a positive amount - we CAN NOT subtract!
                        $newWarehProdQty = $proddelta;
                        //update inventory qty
                        $productStockQty = $productStockQty + $proddelta;
                        //save warehouse qty
                        $transaction->addObject($prodQtyResModel);
                        $prodQtyResModel->setData(array('product_sku'=>$productSku, 'warehouse_id'=>$warehouseId, 'prodqtperwareh'=>$newWarehProdQty))->save();
                        //TODO: is it tray/exception required
                        //$prodQtyResModel->save();
                    } else {
                        $errors = $errors.'SKIP: "'.$productSku.'" does not exist in THIS warehouse: ID='.$warehouseId.', BUT _proddelta_ is negative!<br>';
                    }
                }
                //update mage core inventory
                $transaction->addObject($_stock);
                $_stock->setQty($productStockQty)->setData('is_in_stock',$productStockQty ? 1 : 0)->save();
                //TODO: is it tray/exception required
                //$_stock->save();
            } else {
                //log that product does not exist
                $errors = $errors.'SKIP: "'.$productSku.'" does not exist in Mage DB<br>';
            }
        }
            $transaction->save();
        } catch (Exception $e) 
        {
            $errors = $errors.'FATAL ERROR: '.$e->getMessage();
            return $errors;
        }
        return $errors;
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
        //add form block too but  BELOW grid
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