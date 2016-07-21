<?php
/**
 * Created by PhpStorm.
 * User: SemenetsA
 * Date: 21.07.2016
 * Time: 10:03
 */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('warehousecsv/warehouse'))
    ->addColumn('warehouse_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
    ), 'Warehouse ID')
    ->addColumn('warehousename', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
    ), 'Warehouse Name')
    ->addColumn('location', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'Location')
    ->setComment('Seminc warehousecsv/warehouse entity table');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()->newTable($installer->getTable('warehousecsv/prodquantities'))
    ->addColumn('warehprodquant_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
    ), 'Wareh. Pr. Qt. ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Prod. ID')
    ->addColumn('messagetext', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
    ), 'Message Text')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    ), 'Message Date')
    ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Timestamp')
    ->setComment('Seminc testmod/prodmessage entity table');

$installer->getConnection()->createTable($table);

$installer->endSetup();