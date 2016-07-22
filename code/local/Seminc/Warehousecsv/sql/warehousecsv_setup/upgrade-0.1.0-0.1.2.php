<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 12.07.2016
 * Time: 20:50
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->dropColumn($installer->getTable('warehousecsv/prodquantities'),
        'product_id'
    );
    
$installer->getConnection()
    ->addColumn($installer->getTable('warehousecsv/prodquantities'),
        'product_sku',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'unsigned' => true,
            'nullable' => false,
            'comment' => 'Product SKU'
        )
    );

$installer->endSetup();