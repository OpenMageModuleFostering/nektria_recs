<?php

require_once (Mage::getModuleDir('', 'Nektria_ReCS') . DS . 'lib' . DS .'Nektria.php');

class Nektria_ReCS_Model_Observer
{
	protected $_code = 'nektria_recs';
	private static $logfile = 'shipping_nektria_events.log';

    /**
     * This method save in session the user selection. Magento refresh shipping rates in each step.
     * @param  [type] $observer event parameter
     */
    public function checkout_controller_onepage_save_shipping_method($observer){
    	$this->log('Inside OnePage Save Shipping', 'SaveShipping method');
        //check if carrier is enabled
    	if (! Mage::helper('nektria')->getEnabled() ) {
    		return false;
    	}
    	$this->savelog = Mage::getStoreConfig('debug/options/enable');

        //get Quote from session and rescue shipping method
    	$quote = $checkout = Mage::getSingleton('checkout/session')->getQuote();
    	$address = $quote->getShippingAddress();
    	$shipping_method = $address->getShippingMethod();

        //Switch with method type
                
               $recs = new NektriaSdk();

    	if ($shipping_method == 'nektria_recs_lastmile'){
            		//get nektria lastmile selecction
    		$request = $observer->getRequest();
    		$pickup = $request->getParam('nektria_selection',false);

            		//save in session the user selection
    		//If not saved yet then save, ignore in other case
    		if  ( (! $detectSel = $recs->getUserSelection() ) || ($pickup !== '{}' && $pickup !== $detectSel )  ){
    			self::log($pickup, 'Saving the new nektria shipping selection');
    			$recs->setUserSelection( $pickup );
               	}


    		$recs->validateSecurity(FALSE); 

    	}else if($shipping_method == 'nektria_recs_classic'){
    		$recs->validateSecurity(FALSE);
    	}
        //nothing with other shipping methods...
    }

    /**
     * Saves the shipment to nektria and clean session data
     * @param  variant $observer Event paramenters
     */
    public function checkout_submit_all_after($observer){
        //check if carrier is enabled
    	if (! Mage::helper('nektria')->getEnabled() ) {
    		return false;
    	}

        	//get shipping method
    	$shipping_method = Mage::helper('nektria')->getShippingMethod();

    	//Gets current order id
    	$order = $observer->getEvent()->getOrder();
    	$order_id = $order->getIncrementId();

    	$lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderNektria(false);

    	//checks if the order has been processed one time
    	if (!$lastOrderId || $order_id != $lastOrderId){
    		$apikey = Mage::helper('nektria')->getConfig('apikey');
    		$recs = new NektriaSdk();

    		if ($shipping_method == 'nektria_recs_lastmile'){
                                        //Saves user selection into database
                                            $lastmile_db = Mage::getModel('nektria_recs/lastmile');
                                            $lastmile_db->setOrderId( $order_id );
                                            $lastmile_db->setUserSelection( $recs->getUserSelection() );
                                            $lastmile_db->save();

                	            //If selected method and carrier is nektria and lastmile
    			$recs->saveLastMile($order_id);

    		}else if ($shipping_method == 'nektria_recs_classic'){
	            //If selected method and carrier is nektria and classic

    			$recs->saveClassic($order_id);
    		}
	    	//Save last order id because this event is called 2 times
    		Mage::getSingleton('checkout/session')->setLastOrderNektria( $order_id );
    		$recs->clean();
    	}
    }

    /**
     * Checks if the shipping has been changed for security
     * @param Variant $observer Event param
     */
    public function sales_order_place_before($observer){
    	//check if carrier is enabled
    	if (! Mage::helper('nektria')->getEnabled() ) {
    		return false;
    	}
        	//if validate security stops the payment process
    	$recs = new NektriaSdk();
    	//Check errors
    	if (Mage::helper('nektria')->getLastMileSelected() && $recs->validateSecurity())
    		Mage::throwException(Mage::helper('nektria')->__('Shipping method choices have timed out, please select your delivery windows again'));

    	//Forbidden offline payment methods
    	if (Mage::helper('nektria')->getLastMileSelected() && ! Mage::helper('nektria')->checkAllowPaymentMethod( Mage::helper('nektria')->getPaymentMethod() ) )
    		Mage::throwException(Mage::helper('nektria')->__('Last Mile shipping method is not compatible with the selected payment method'));

    	//Check last validation lastmile before payment
    	if (Mage::helper('nektria')->getLastMileSelected() && !$recs->validateLastMile())
    		Mage::throwException(Mage::helper('nektria')->__('An error has ocurred during Last Mile Shipping Method validation. Please change your selection'));
    }

    /**
     * Cleans session data
     * @param  variant $observer Event paramenters
     */
    public function checkout_quote_destroy($observer){
        //check if carrier is enabled
    	if (! Mage::helper('nektria')->getEnabled() ) {
    		return false;
    	}
        //remove sessions variables from nektria shipping
    	$recs = new NektriaSdk();
    	$recs->clean();
    }


    /* ************************************************************************************** */
    static function log($obj, $msg=''){
    	if (! Mage::getStoreConfig('dev/log/active')) 
    		return FALSE;
    	Mage::helper('nektria')->log($obj, $msg, self::$logfile);
    }

    /** Logging events debugger */
    public function controller_action_predispatch($observer) { 
    	self::log( 
            'event', //$observer , 
            $observer->getEvent ()->getControllerAction ()->getFullActionName ()
            );
    }
}