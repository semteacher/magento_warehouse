<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 20:37
 */
class Seminc_Warehousecsv_Block_Adminhtml_Warehouse_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('seminc_warehousecsv_warehouse_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'warehousecsv/warehouse_collection';
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
        $this->addColumn('warehouse_id',
            array(
                'header'=> $this->__('Warehouse ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'warehouse_id'
            )
        );

        $this->addColumn('warehousename',
            array(
                'header'=> $this->__('Warehouse Name'),
                'index' => 'warehousename'
            )
        );

        $this->addColumn('location',
            array(
                'header'=> $this->__('Location'),
                'index' => 'location'
            )
        );

        /**
         * Finally, we'll add an action column with an edit link.
         */
        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array(
                        'base' => 'adminhtml/warehouse/edit',
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'entity_id',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}