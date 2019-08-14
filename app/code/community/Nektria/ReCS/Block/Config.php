<?php

require_once (Mage::getModuleDir('', 'Nektria_ReCS') . DS . 'lib' . DS .'Nektria.php');
/**
 * Sets the config button in the extension setup
 */
class Nektria_Recs_Block_Config extends Mage_Adminhtml_Block_System_Config_Form_Field
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

	   $url = $recs->getBackendUrl();
 
 	   $html = '<input onclick="window.open(\''.$url.'\', \'\', \'location=no,menubar=no,toolbar=no,width=600,height=400\');" type="button" value="'.$this->__('Nektria Configuration').'" class="button" />';
	   return $html;
   }
}