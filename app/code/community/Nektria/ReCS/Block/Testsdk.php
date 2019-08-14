<?php
/**
 * Set the block to test configuration in the extension method setup
 */
class Nektria_Recs_Block_Testsdk extends Mage_Adminhtml_Block_System_Config_Form_Field
{

   /**
	* Returns html part of the setting
	*
	* @param Varien_Data_Form_Element_Abstract $element
	* @return string
	*/
   protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
   {
	   $this->setElement($element);
	   $recs = new NektriaSdk();

	   $errorCode = NULL;
	   $working = $recs->testRequest($errorCode);
	   $errorString = '';

	   //detect only auth error by now
	   switch ($errorCode) {
	   	case 401:
	   		$errorString = $this->__('error connecting to the service, check it the API Key');
	   		break;	   	
	   	default:
	   		$errorString = $this->__('error connecting to the service');
	   		break;
	   }

 	   $html = '<span style=\'font-size: 0.9em;\'>'.
 	   		((Mage::helper('nektria')->getConfig('sandbox') || Mage::helper('nektria')->checkDemoFlag() )?$this->__('Testing environment'):$this->__('Production environment')).': '.
 	   		(($working)?$this->__('service configured correctly'):$errorString). 	   		
 	   		'</span>'.
 	   		((Mage::helper('nektria')->checkDemoFlag())?'<p style=\'font-size: 0.9em; font-weight: bold;\'>'.$this->__('Your site is in demo mode, ReCS is blocked in sandbox mode').'</p>':'');
	   return $html;
   }
}