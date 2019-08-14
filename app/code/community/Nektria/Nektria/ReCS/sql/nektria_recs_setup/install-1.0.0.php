<?php
/**
 * Setup procedure for Nektria ReCS extension
 */

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
	->newTable( $installer->getTable('nektria_recs/lastmile') )
	->addColumn('lastmile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'identify' => true,
		'nullable' => false,
		'primary' => true
		), 'LastMile id')
	->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 60, array(
		'nullable' => false
		), 'Order id')
	->addColumn('user_selection', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
		'nullable' => false
		), 'UserSelection')
	->addIndex( $installer->getIdxName(
		$installer->getTable('nektria_recs/lastmile'),
		array('order_id'),
		Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
		) ,
		array('order_id'),
		array( 'type' =>Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
	)
	->setComment( 'User Selection from Nektria ReCS' );

$installer->getConnection()->createTable( $table );

$installer->endSetup();