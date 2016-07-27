<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 20:37
 */
class Seminc_Warehousecsv_Block_Adminhtml_Prodquantities_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('seminc_warehousecsv_prodquantities_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'warehousecsv/prodquantities_collection';
    }

    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass())->addProductName();
        $collection->addWarehouseName();
        $collection->addExpressionFieldToSelect('prod_total_qty', 'SUM(prodqtperwareh)', array('prodqtperwareh'));
        $collection->addExpressionFieldToSelect('warehouses', 'GROUP_CONCAT({{warname}} SEPARATOR ", ")', array('warname'=>'wareh.warehousename'));
        $collection->getSelect()->group('product_sku');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('product_name',
            array(
                'header' => $this->__('Product Name'),
                'width' => '150px',
                'index' =>'product_name',
                'filter_index' =>'cpev.value'
        )
        );

        $this->addColumn('product_sku',
            array(
                'header'=> $this->__('Product SKU'),
                'width' => '50px',
                'index' => 'product_sku'
            )
        );

        $this->addColumn('prod_total_qty',
            array(
                'header'=> $this->__('Total Prod. Qty.'),
                'width' => '50px',
                'index' => 'prod_total_qty'
            )
        );

        $this->addColumn('warehouses',
            array(
                'header' => $this->__('Warehouses'),
                'width' => '150px',
                'index' =>'warehouses',
                'filter_index' =>'wareh.warehousename'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}