<?php

class Nektria_ReCS_Model_Paymentmethods
{
    public function toOptionArray()
    {
        return Mage::helper('nektria')->getDirectPaymentMethods();
    }
}
