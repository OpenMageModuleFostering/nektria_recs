<?php
/**
 * 
 */

class Nektria_ReCS_Model_Resource_Lastmile extends Mage_Core_Model_Resource_Db_Abstract
{
	// Initialize connection and define main table and primary key
	protected function _construct(){
		$this->_init( 'nektria_recs/lastmile', 'lastmile_id' );
	}
}