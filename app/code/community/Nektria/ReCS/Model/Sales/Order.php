<?php
class Nektria_ReCS_Model_Sales_Order extends Mage_Sales_Model_Order{
    public function getShippingDescription(){
        $desc = parent::getShippingDescription();

        $userSelectionJSON = Mage::getSingleton('checkout/session')->getNektriaUserSelection(FALSE);
        if (!$userSelectionJSON) {
        	$userSelection = Mage::getModel('nektria_recs/lastmile')->load($this->getIncrementId(),'order_id');
        	$userSelectionJSON = $userSelection->getUserSelection();
        }

		try{
			$selectedTime = Mage::helper('core')->jsonDecode($userSelectionJSON);
		}catch(Exception $e){
			$selectedTime = array();
		}

		if ($selectedTime && count($selectedTime)){
	        $lastmile = Mage::app()
	        	->getLayout()
	        	->createBlock('core/template')
	        	->setData('selection', $selectedTime)
	        	->setTemplate('recs/email/lastmile.phtml')
	        	->toHtml();
	        return $lastmile.$desc;
		}else{
			return $desc;
		}		
    }
}