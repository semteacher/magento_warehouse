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
            $transaction = Mage::getModel('core/resource_transaction')
            //process all rows in csv
        foreach ($data as $key=>$row) {
            $prodname = $row[0];
            $proddelta = (int) $row[1];
            $warehname = $row[2];
            //echo "<br><h3>".$key.". prodname=".$row[0]." delta=".$row[1]." wareh.=".$row[2]."</h3>";
            //load warehouse model
            $_warehouse = Mage::getModel('warehousecsv/warehouse')->load($warehname,'warehousename');
            if (!($_warehouse->getId())) {
                //create a new warehouse
                $_warehouse->setWarehousename($warehname)->save();
                //TODO: tray/exception required
                //$_warehouse->save();
                //echo "<br>create warehouse ".$warehname;
            }
            $warehouseId = $_warehouse->getId();
            //echo "<br>current warehouse ID=".$warehouseId;
            //load product model
            $_product = Mage::getModel('catalog/product')->loadByAttribute('name',$prodname);
            //process only existing product object
            if (is_object($_product)){
                //echo "<br>current product ID=";
                //var_dump($_product->getId());
                //get product SKU
                $productSku = $_product->getSku();
                //echo "<br>get product SKU=".$productSku;
                //get product inventory quantity
                $_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                $productStockQty = $_stock->getQty();
                //echo "<br>get product inventory QTY=".$product_qty;
                //get current product total qty in all warehouses
                //$_prodquantities_collection = Mage::getModel('warehousecsv/prodquantities')->getCollection()
                //    ->addExpressionFieldToSelect('total_qty', 'SUM(prodqtperwareh)', array('prodqtperwareh'))
                //    ->addFieldToFilter('productSku', $productSku);
                //$prodQtyTotal = $_prodquantities_collection->getFirstItem()->getData('total_qty');
                $prodQtyTotal = Mage::helper('warehousecsv')->getWarehouseProductTotalQty($productSku);
                //echo "<br>get product total qty in warehouse module=".$prodQtyTotal;
                //used for the first time only, really:
                //При первом запуске подразумевается, что ни на одном из складов нет ни одного продукта.
                if ($prodQtyTotal <> $productStockQty) {
                    $productStockQty = $prodQtyTotal;
                }
                //load produqntites model
                //$_prodquantities_col = Mage::getModel('warehousecsv/prodquantities')->getCollection()
                //    ->addFieldToFilter('warehouseId', $warehouseId)
                //    ->addFieldToFilter('productSku', $productSku);
                //$prodQtyResModel = $_prodquantities_col->getFirstItem();
                $prodQtyResModel = getWarehouseProductQtyResModel($warehouseId, $productSku);
                if ($prodQtyResModel->getId()){
                    //the item exists in warehouse module
                    $newWarehProdQty = $prodQtyResModel->getProdqtperwareh()+ $proddelta;
                    //echo "<br>prepare change existing product qty from ".$prodQtyResModel->getProdqtperwareh()." to ".$newWarehProdQty;
                    if ($newWarehProdQty < 0) {
                        $newWarehProdQty = 0;
                        //update inventory qty
                        $productStockQty = $productStockQty - $prodQtyResModel->getProdqtperwareh();
                        //TODO: tray/exception required?
                        $prodQtyResModel->delete();
                        //echo "<br>zero - delete existing warehouse module product qty record";
                    } else {
                        //update inventory qty
                        $productStockQty = $productStockQty + $proddelta;
                        //save warehouse qty
                        $prodQtyResModel->setData(array('product_sku'=>$productSku, 'warehouse_id'=>$warehouseId, 'prodqtperwareh'=>$newWarehProdQty))->save();;
                        //TODO: tray/exception required
                        //$prodQtyResModel->save();
                        //echo "<br>save change existing product qty";
                    }
                }
                else {
                    //the item does not exist in warehouse module - update through mage core inventory
                    if ($proddelta > 0) {
                        //we can just ADD a positive amount - we CAN NOT subtract!
                        $newWarehProdQty = $proddelta;
                        //update inventory qty
                        $productStockQty = $productStockQty + $proddelta;
                        //echo "<br>prepare create new product qty from ZERO to ".$newWarehProdQty;
                        //save warehouse qty
                        $prodQtyResModel->setData(array('product_sku'=>$productSku, 'warehouse_id'=>$warehouseId, 'prodqtperwareh'=>$newWarehProdQty))->save();
                        //TODO: tray/exception required
                        //$prodQtyResModel->save();
                        //echo "<br>save change new product qty";

                    } else {
                        $errors = $errors.'SKIP: "'.$prodname.'" does not exist in THIS warehouse but _proddelta_ is negative!<br>';
                        //echo "<br>".$errors;
                    }
                }
                //update mage core inventory
                //$_stock->setQty($productStockQty);
                $_stock->setData('qty'=>$productStockQty,'is_in_stock'=>$productStockQty ? 1 : 0)->save();
                //TODO: tray/exception required
                //$_stock->save();
                //echo "<br>save change mage inventory update to ".$productStockQty;
            } else {
                //exception/log that product does not exist
                $errors = $errors.'SKIP: "'.$prodname.'" does not exist in Mage DB<br>';
                //echo "<br>".$errors;
            }
        }
            $transaction->save();
        } catch (Exception $e) 
        {
            $errors = $errors.'FATAL ERROR: '.$e->getMessage();
            //echo "<br>".$errors;
            return $errors;
        }
        //die($errors);
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