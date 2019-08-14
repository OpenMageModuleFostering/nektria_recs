<?php

/**
 * Not used actually, prepared for AJAX test button, used on save instead
 */
class Nektria_ReCS_SdkController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
    	echo Mage::helper('core')->jsonEncode(array('stat'=>'OK', 'action'=>'index'));
    }
    public function testAction(){
    	echo Mage::helper('core')->jsonEncode(array('stat'=>'OK', 'action'=>'test'));
    }
}