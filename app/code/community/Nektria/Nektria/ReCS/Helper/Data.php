<?php

require_once (Mage::getModuleDir('', 'Nektria_ReCS') . DS . 'lib' . DS .'Nektria.php');

class Nektria_ReCS_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CONFIG_KEY = 'carriers/nektria_recs';
	const CARRIER = 'nektria_recs';
	const ASSETS_VERSION = 1;
	const CONNECT_TIMEOUT = 1.5;
	const TIMEOUT = 2;

	/**
	 * Return checkout config value by key and store
	 *
	 * @param string $key
	 * @return variant|null
	 */
	public function getConfig($key='', $store = null)
	{
		if( !isset ( $this->_configs) ){
			$this->_configs = Mage::getStoreConfig(self::CONFIG_KEY, $store);
		}
		return (isset($this->_configs[$key])?$this->_configs[$key] : NULL );
	}

	public function getReCSMethods(){
		return array(
				'lastmile' => $this->__('Elige día y hora'),
				'classic' => $this->__('RECS: Servicio Estándar')
		);
	}

	/**
	 * Get configured parameters of the service from backend
	 * @param  array 	$params of configuration
	 * @return array 	Merged params with defaults
	 */
	public function getServiceParams($params){
		$recs = new NektriaSdk();

		$return = array_merge( array(
			'APIKEY' => self::getConfig('apikey'),
			'environment'=>'production',
			'timeout' => self::TIMEOUT,
    		'connect_timeout' => self::CONNECT_TIMEOUT
			), $params );

		//if demo or sandbox then get SandboxKey
		if (self::getConfig('sandbox') || self::checkDemoFlag()){
			$return['APIKEY'] = $recs->getSandboxApiKey();
			$return['environment'] = 'sandbox';
		}

		return $return;
	}

	/**
	 * Get  carrier name
	 * @return string
	 */
	public function getCode(){
		return self::CARRIER;
	}

	/**
	 * Get the number of assets version
	 * @return int the version
	 */
	public function getAssetsVersion(){
		return self::ASSETS_VERSION;
	}

	/**
	 * Get if the extension is active and get an apikey from config
	 * @return bool
	 */
	public function getEnabled(){
		return  (Mage::helper('nektria')->getConfig('active') && ! is_null(Mage::helper('nektria')->getConfig('apikey')) );
	}

	/**
	 * Get if LightCheckout is installed and enabled
	 * @return bool
	 */
	public function getGomageLightCheckoutEnabled(){
		return Mage::getStoreConfig('gomage_checkout/general/enabled');
	}

	/**
	 * Get the last timeWindow selection for Last Mile used in template view
	 * @return JSON Stringify
	 */
	public function getLastSelection(){
		return Mage::getSingleton('checkout/session')->getNektriaUserSelection(FALSE);
	}

	/**
	 * Get if nektria last mile shipping method is selected
	 * @return bool
	 */
	public function getLastMileSelected(){
	    	$shipping_method = self::getShippingMethod();

	        //Switch with method type
	    	if ($shipping_method == 'nektria_recs_lastmile'){
	    		return TRUE;
	    	}else{
	    		return FALSE;
	    	}
	}

	/**
	 * Returns the last Mile selected Price
	 * @return string price with currency code
	 */
	public function getLastMileSelectedPrice(){
		if ( self::getLastMileSelected() ){
			$selectedPrice = Mage::helper('core')->jsonDecode(self::getLastSelection());
			return Mage::helper('core')->currency( $selectedPrice['total_price'] );
		}else{
			return '';
		}
	}

	/**
	 * Get the selected shipping method
	 * @return bool
	 */
	public function getShippingMethod(){
		$quote = $checkout = Mage::getSingleton('checkout/session')->getQuote();
	    	$address = $quote->getShippingAddress();
	    	return $address->getShippingMethod();
	}

	/**
	 * Get the selected payment method
	 * @return string code of the method
	 */
	public function getPaymentMethod(){
		$quote = $checkout = Mage::getSingleton('checkout/session')->getQuote();
		return $quote->getPayment()->getMethodInstance()->getCode();
	}

	/**
	 * Get direct payment methods from magento usetting offline methods
	 * @return array of payment methods
	 */
	public function getDirectPaymentMethods(){
		$methods = Mage::helper('payment')->getPaymentMethodList(TRUE, TRUE, TRUE);
        		unset($methods['offline']);
        		return $methods;
	}

	/**
	 * Returns the list of offline payment methods names
	 * @return array of names
	 */
	public function getOfflinePaymentMethods(){
		$methods = Mage::helper('payment')->getPaymentMethodList(TRUE, TRUE, TRUE);
		$methods = array_keys( $methods['offline']['value'] );

		return $methods;
	}

	/**
	 * Get backend disabled by the admin payment methods
	 * @return array
	 */
	public function getDisabledPaymentMethods(){
		$paymentsallow = self::getConfig('paymentsallow');
		//convert variable to array
		$paymentsallow = ($paymentsallow)?explode( ',', $paymentsallow ) : array();

		//returns selected direct payment methods and all offline payment methods
		return array_merge( $paymentsallow, self::getOfflinePaymentMethods() );
	}

	/**
	 * Check if the payment method selected allow Nektria Last Mile
	 * @param  string $method Payment method
	 * @return bool if allow this method for last mile
	 */
	public function checkAllowPaymentMethod($method){
		if ( in_array( $method, self::getDisabledPaymentMethods() ) ){
			return FALSE;
		}else{
			return TRUE;
		}
	}

	/**
	 * Write html template with the Last Mile Selection
	 * @return string html
	 */
	public function htmlLastMileSelection(){
		$template =  Mage::app()->getLayout()->createBlock('core/template')
        	->setTemplate('recs/totals/lastmile.phtml');
        return $template->toHtml();
	}

	/**
	 * Write html template with the Last Mile Selection
	 * @return string html
	 */
	public function htmlAdminLastMileSelection($order){
		$template =  Mage::app()->getLayout()->createBlock('core/template')
			->setData('order', $order)
        	->setTemplate('recs/sales/order/view/lastmile.phtml');
        return $template->toHtml();
	}

	/**
	 * Returns if array1 is different to array2
	 * @param  array $array1 
	 * @param  array $array2 
	 * @return bool
	 */
	public function checkChanges($array1, $array2){
		$original = count($array1);
		if ($original !== count($array2)){
			return TRUE;
		}

		$result = count(array_intersect($array1, $array2));

		if( $original == $result){
			return FALSE;
		}else{
			return TRUE;
		}
	}

	/**
	 * Helper function for translations
	 * @param  string $string Traslate to string
	 * @return string translation
	 */
	public function _($string){
		return self::__($string);
	}

	/**
	 * Returns the session timeout in seconds
	 * @param  int store id
	 * @return int seconds
	 */
	public function getSessionTimeout($store = NULL){
		return Mage::getStoreConfig( 'web/cookie/cookie_lifetime' , $store);
		
	}

	public function checkDemoFlag(){
		$sTestDefault = Mage::getStoreConfig('design/head/demonotice');

		if ($sTestDefault){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/**
	 * Log into file var exporting
	 * @param  var $object 
	 * @param  string $title  The title of the log section
	 * @param  string $file  the name of the log file
	 * @return void
	 */
	public function log($object, $title = NULL, $file = NULL){
		if ($title){
			Mage::log('----------------------'.$title.'--------------------------', null, $file);
		}
		Mage::log(var_export($object, TRUE), null, $file);
	}

	/**
	 * Gets the currency symbol associated to a Currency Code
	 * @return string currency symbol
	 */
	public function getCurrencySymbol(){
		$currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
		return Mage::app()->getLocale()->currency( $currency_code )->getSymbol();
	}
}