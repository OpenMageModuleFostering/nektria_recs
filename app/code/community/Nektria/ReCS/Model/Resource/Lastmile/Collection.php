<?php
/**
 * 
 */

class Nektria_ReCS_Model_Resource_Lastmile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	// Initialize connection and define main table and primary key
	protected function _construct(){
		$this->_init( 'nektria_recs/lastmile' );
	}
}