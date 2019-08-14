<?php
class Nektria_ReCS_Model_Sales_Order extends Mage_Sales_Model_Order{
    public function getShippingDescription(){
        $desc = parent::getShippingDescription();
        // Mage::helper('nektria')->log($userSelection, 'userSelection', 'test.log');
        // Mage::helper('nektria')->log($this->getIncrementId(), 'getIncrementId', 'test.log');
        // Mage::helper('nektria')->log($json, 'json', 'test.log');
        // Mage::helper('nektria')->log(Mage::getSingleton('checkout/session')->getNektriaUserSelection(FALSE), 'json from Session', 'test.log');
        $lastmile = Mage::app()->getLayout()->createBlock('core/template')->setTemplate('recs/email/lastmile.phtml');

        return $lastmile->toHtml().$desc;
    }
}