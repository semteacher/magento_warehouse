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
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('warehprodquant_id',
            array(
                'header'=> $this->__('Wareh. Pr. Qt. ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'warehprodquant_id'
            )
        );

        $this->addColumn('prodqtperwareh',
            array(
                'header'=> $this->__('Prod. Qt.'),
                'width' => '50px',
                'index' => 'prodqtperwareh'
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