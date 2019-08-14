<?php

require_once (Mage::getModuleDir('', 'Nektria_ReCS') . DS . 'lib' . DS .'Nektria.php');

class Nektria_Recs_Block_Registration extends Mage_Adminhtml_Block_System_Config_Form_Field
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

	   $url = $recs->getRegistrationUrl();
	   $nektria_signup = $this->__('Nektria Signup');
	   $html = <<<EOT
<script type="text/javascript">
function nektria_registration_onload(){
	$.noConflict();
	jQuery("#carriers_nektria_recs_apikey").change(function(){
		if (this.value != ''){
			jQuery("#row_carriers_nektria_recs_registration").hide();
		}else{
			jQuery("#row_carriers_nektria_recs_registration").show();
		}
	});
}

if(typeof(jQuery)== "undefined"){
	var script = document.createElement("script");
	script.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js";
	script.onload = nektria_registration_onload;
	document.getElementsByTagName('head')[0].appendChild(script);
}else{
	jQuery(document).ready(nektria_registration_onload);
}


</script>
<input onclick="window.open('$url', '', 'location=no,menubar=no,toolbar=no,width=600,height=400');" type="button" value="$nektria_signup" class="button" />
EOT;

	   return $html;
   }
}