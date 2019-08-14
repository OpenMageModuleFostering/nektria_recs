<?php

require_once('vendor/autoload.php');

class NektriaExtensionException extends Exception{
	public function getError(){
		return FALSE;
	}
}

class NektriaSdk{
	protected $id = NULL;
	protected $logfile = 'nektria.sdk.log';
	protected $lastError = NULL;
	protected $lastPrice = NULL;
	protected $assets = NULL;

	protected $options = array(
		'APIKEY' => '',  //Sandbox key
		'secure' => true,
		'environment'=> 'sandbox'
		);
	protected $lastResponse = NULL;

	function __construct(){}

	public function setOptions(array $options){
		$this->options = array_merge($this->options, $options);

		if ($this->options['environment'] == 'sandbox'){
			$this->options['APIKEY'] = $this->getSandboxApiKey();
		}
		$this->log($this->options,'setOptions');
		return TRUE;
	}

	/**
	 * Gets call options of the service
	 * @return array The options saved
	 */
	public function getOptions(){
		$lastServiceId = Mage::getSingleton('checkout/session')->getNektriaServiceNumber();
		if ($lastServiceId)
			$this->id = $lastServiceId;

		$this->options = array_merge( 
			$this->options , 
			Mage::helper('nektria')->getServiceParams(array()) 
			);

		$this->log(array('options'=>$this->options ,'id'=>$this->id),'getOptions and id');

		if (! is_null($this->id))
			return array_merge($this->options, array('id'=>$this->id));
		else
			return $this->options;
	}

	/**
	 * Cleans Nektria session variables
	 * @return void
	 */
	public function clean(){
		$this->log(FALSE, 'Called Clean Method');
		//remove sessions variables from nektria shipping
		Mage::getSingleton('checkout/session')->unsNektriaUserSelection();
		Mage::getSingleton('checkout/session')->unsNektriaServiceNumber();
		Mage::getSingleton('checkout/session')->unsNektriaServiceType();
		Mage::getSingleton('checkout/session')->unsNektriaLastPostalCode();
		Mage::getSingleton('checkout/session')->unsNektriaLastCountryCode();
		Mage::getSingleton('checkout/session')->unsClassicPrice();
		Mage::getSingleton('checkout/session')->unsPriceMatrix();
		Mage::getSingleton('checkout/session')->unsNektriaSecurityError();
		Mage::getSingleton('checkout/session')->unsOldLastmileRequest();
		Mage::getSingleton('checkout/session')->unsOldClassicRequest();
		Mage::getSingleton('checkout/session')->unsCoveredCountries();
		Mage::getSingleton('checkout/session')->unsNektriaBackendUrl();
		Mage::getSingleton('checkout/session')->unsNektriaRegistrationUrl();
		Mage::getSingleton('checkout/session')->unsNektriaLastShippingAddress();
		Mage::getSingleton('checkout/session')->unsNektriaLastSubtotal();

		$this->id=NULL;
		$this->lastResponse=NULL;
		$this->lastError=NULL;
		$this->lastPrice=NULL;
		$this->assets=NULL;
	}


	/**
	 * Get the Subtotal saved in session
	 * @return string Subtotal
	 */
	public function getLastSubtotal(){
		return Mage::getSingleton('checkout/session')->getNektriaLastSubtotal(FALSE);
	}

	/**
	 * Get the LastShipping Address saved in the session
	 * @return int ServiceID
	 */
	public function getLastShippingAddress(){
		$return = Mage::getSingleton('checkout/session')->getNektriaLastShippingAddress(FALSE);

		if(!$return){
			return array();
		}else{
			return unserialize($return);
		}
	}

	/**
	 * Sets user LastMile Selection in the session
	 * @param string JSON stringify
	 */
	public function setUserSelection($pickup){
		$this->log($pickup,'setUserSelection');
		//ignore default value
		if($pickup && $pickup != '{}')
			Mage::getSingleton('checkout/session')->setNektriaUserSelection($pickup);
	}

	/**
	 * Returns LastMile user selection from the session
	 * @return string JSON Stringify
	 */
	public function getUserSelection(){
		$userSelection = Mage::getSingleton('checkout/session')->getNektriaUserSelection(FALSE);
		$this->log($userSelection, 'getUserSelection');
		return (string) $userSelection;
	}	

	/**
	 * Get the last response of the API Call
	 * @return Variant the last response
	 */
	public function getLastResponse(){
		return $this->lastResponse;
	}

	/**
	 * Get the ServiceID saved in the session
	 * @return int ServiceID
	 */
	public function getServiceId(){
		return ($this->id)?
			$this->id :
			Mage::getSingleton('checkout/session')->getNektriaServiceNumber(FALSE);
	}

	/**
	 * Get the serviceType returned "classic|last-mile"
	 * @return string ServiceType
	 */
	public function getServiceType(){
		return Mage::getSingleton('checkout/session')->getNektriaServiceType(FALSE);
	}

	/**
	 * Get the Postal Code from the session
	 * @return string PostalCode
	 */
	public function getLastPostalCode(){
		return Mage::getSingleton('checkout/session')->getNektriaLastPostalCode(FALSE);
	}

	/**
	 * Get the Country code from the session
	 * @return string Country Code
	 */
	public function getLastCountryCode(){
		return Mage::getSingleton('checkout/session')->getNektriaLastCountryCode(FALSE);
	}

	/**
	 * get the LastError generated
	 * @return Exception error
	 */
	public function getLastError(){
		return $this->lastError;
	}

	/**
	 * Get the priceMatrix of LastMile execution
	 * @return object PriceMatix
	 */
	public function getPriceMatrix(){
		return Mage::getSingleton('checkout/session')->getPriceMatrix(FALSE);
	}

	/**
	 * Get the lastPrice returned from Classic or LastMile availability
	 * @return float Price
	 */
	public function getLastPrice(){
		return $this->lastPrice;
	}

	/**
	 * Check or set security checks
	 */
	public function validateSecurity($setValue = NULL){
		if ($setValue){
			Mage::getSingleton('checkout/session')->setNektriaSecurityError( $setValue );
		}else if(is_null($setValue)){
			return Mage::getSingleton('checkout/session')->getNektriaSecurityError(FALSE);
		}else{
			Mage::getSingleton('checkout/session')->unsNektriaSecurityError();
		}
	}

	/* ------------------------------------------------- API CALLS -------------------------------------------------------- */

	/**
	 * Call to ServiceCreationRequest and saves values in sesion
	 * @param  array  $nektriaParams User and shipping data
	 * @return bool true or false if success
	 */
	public function createService(array $nektriaParams){
		//Add cookie session
		$nektriaParams['session_timeout'] = intval( Mage::helper('nektria')->getSessionTimeout() );
		//Add current currency code
		$nektriaParams['currency_code'] = Mage::app()->getStore()->getBaseCurrencyCode();

		//Send the request of service Nektria SDK
		$this->log($nektriaParams,'User and Shipping Address for createService');
		$sr = new Nektria\Recs\MerchantApi\Requests\ServiceCreationRequest($this->getOptions());
		
		try{
			$this->lastResponse = $sr->execute($nektriaParams);
			$this->log($this->lastResponse, 'createService response');
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'createService ERROR');
			return FALSE;
		}
			
		//Saving service_id in the session 
		$this->id = $this->lastResponse->getServiceNumber();
		Mage::getSingleton('checkout/session')->setNektriaLastShippingAddress( serialize( $nektriaParams['destination_address'] ));
		Mage::getSingleton('checkout/session')->setNektriaServiceNumber($this->id);
		Mage::getSingleton('checkout/session')->setNektriaServiceType( $this->lastResponse->getServiceType() );
		Mage::getSingleton('checkout/session')->setNektriaLastPostalCode($nektriaParams['destination_address']['postal_code']);
		Mage::getSingleton('checkout/session')->setNektriaLastCountryCode($nektriaParams['destination_address']['country_code']);
		Mage::getSingleton('checkout/session')->setNektriaLastSubtotal($nektriaParams['total_price']);

		$this->log($nektriaParams['total_price'],'Set last subtotal');
					

		return TRUE;
	}

	/**
	 * Remains nektria session alive
	 * @return bool true or false if success
	 */
	public function keepAlive(){
		$lastKeepAlive = Mage::getSingleton('checkout/session')->getLastKeepAlive(FALSE);

		//if last KeepAlive was more than five minutes then callit
		if($lastKeepAlive && ( (time() - (int)$lastKeepAlive) < (60*5) )){
			return TRUE;
		}

		try{
			$kar = new Nektria\Recs\MerchantApi\Requests\KeepAliveRequest($this->getOptions());
			$this->lastResponse = $kar->execute();
			$this->log($this->lastResponse, 'keepAlive');
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'keepAlive ERROR');
			return FALSE;
		}
		
		$this->keepAliveCalled = TRUE;
		Mage::getSingleton('checkout/session')->setLastKeepAlive(time());
		return TRUE;
	}

	/**
	 * Call to LastMile availability
	 * @return object Response from the method
	 */
	public function lastMileAvailabilityRequest(){
		try{
			$lmar = new Nektria\Recs\MerchantApi\Requests\LastMileAvailabilityRequest($this->getOptions());
			$this->lastResponse = $lmar->execute(array("service_type" => $this->getServiceType() ));
			$this->log($this->lastResponse, 'lastMileAvailabilityRequest');
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'lastMileAvailabilityRequest ERROR');
			return FALSE;
		}
		$lastPriceMatrix = Mage::getSingleton('checkout/session')->getPriceMatrix(FALSE);
		if (! $lastPriceMatrix){
			Mage::getSingleton('checkout/session')->setPriceMatrix($this->lastResponse->getPriceMatrix());
		}
		$this->lastPrice = $this->lastResponse->getBestPrice()->getAmount();

		$this->setAvailabilityRequest('lastmile', array(
			'available'=>$this->lastResponse->isAvailable(),
			'price'=>$this->lastPrice,
			'currency_code'=>$this->lastResponse->getBestPrice()->getCurrency(),
			'priceMatrix'=>Mage::getSingleton('checkout/session')->getPriceMatrix(FALSE)
		) );

		return $this->lastResponse;
	}

	public function lastMileBestPriceRequest($params){
		try{
			$scr = new Nektria\Recs\MerchantApi\Requests\LastMileBestPriceRequest($this->getOptions());

			$this->lastResponse = $scr->execute($params);
			$this->log($this->lastResponse, 'lastMileBestPriceRequest');
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'lastMileAvailabilityRequest ERROR');
			return FALSE;
		}

		return array(
			'best_price' => $this->lastResponse->getBestPrice(),
			'currency_code' => $this->lastResponse->getBestPriceCurrency(),
			'best_price_currency' => $this->lastResponse->getBestPriceCurrency()
			);
	}

	/**
	 * Save the lastMileAvailability or classicAvailability Request stored from session
	 * @param string classic | lastmile
	 * @return  void
	 */
	public function setAvailabilityRequest($type, $values){
		$method = 'setOld'.ucwords( $type).'Request';
		Mage::getSingleton('checkout/session')->$method(serialize($values));
	}

	/**
	 * Get the lastMileAvailability or classicAvailability Request stored from session
	 * @param  string classic | lastmile
	 * @return associative array
	 */
	public function getAvailabilityRequest($type){
		$response = array();

		$method = 'getOld'.ucwords( $type).'Request';
		$response = Mage::getSingleton('checkout/session')->$method(FALSE);

		return ($response)?unserialize($response):FALSE;
	}

	/**
	 * Calls to Classic availability
	 * @return object Response from the method
	 */
	public function classicAvailabilityRequest(){
		try{
			$lmar = new Nektria\Recs\MerchantApi\Requests\ClassicAvailabilityRequest($this->getOptions());
			$this->lastResponse = $lmar->execute(array("service_type" => $this->getServiceType() ));
			$this->log($this->getServiceType(), 'classicAvailabilityRequest type');		
			$this->log($this->lastResponse, 'classicAvailabilityRequest');			
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'classicAvailabilityRequest ERROR');
			return FALSE;
		}
		$this->lastPrice = $this->lastResponse->getPrice()->getAmount();
		Mage::getSingleton('checkout/session')->setClassicPrice($this->lastResponse->getPrice());

		$this->setAvailabilityRequest('classic', array(
			'available'=>$this->lastResponse->isAvailable(),
			'currency_code'=>$this->lastResponse->getPrice()->getCurrency(),
			'price'=>$this->lastPrice
		));
		return $this->lastResponse;
	}

	/**
	 * Sends Validation and Confirmation for LastMile
	 * @param  int    $order_id 
	 * @return  bool true or false if success
	 */
	public function saveLastMile($order_id){
		$this->log($order_id, 'Order ID for saveLastMile');
	
		try{
			$lmcr = new Nektria\Recs\MerchantApi\Requests\LastMileConfirmationRequest($this->getOptions());
			$this->lastResponse = $lmcr->execute(array(
				"order_number" => $order_id
				));	
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'LastMileConfirmationRequest ERROR');
			return FALSE;
		}

		$this->log($this->lastResponse, 'saveLastMile');
		return TRUE;
	}

	public function validateLastMile(){
		$userSelection = $this->getUserSelection();
		$this->log($userSelection, 'UserSelection for validateLastMile');
		try{
			$lmvr = new Nektria\Recs\MerchantApi\Requests\LastMileValidationRequest($this->getOptions());
			$this->lastResponse = $lmvr->execute($userSelection);	
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'LastMileValidationRequest ERROR');
			return FALSE;
		}

		$this->log($this->lastResponse, 'validateLastMile');
		return TRUE;
	}

	/**
	 * Sends Classic Confirmation
	 * @param  int    $order_id
	 * @return  bool true or false if success
	 */
	public function saveClassic($order_id){
		$this->log($order_id, 'Order ID for saveClassic');
		try{
			$ccr = new Nektria\Recs\MerchantApi\Requests\ClassicConfirmationRequest($this->getOptions());
			$this->lastResponse = $ccr->execute(array("order_number" => $order_id));
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'saveClassic ERROR');
			return FALSE;
		}

		$this->log($this->lastResponse, 'saveClassic');
		return TRUE;
	}

	/**
	 * get Nektria Assets and save in session
	 * @return bool check if error
	 */
	public function assetsRequest(){
		//ObjectCache
		if ( Mage::getSingleton('checkout/session')->getNektriaJs(FALSE) ){
			return TRUE;
		}

		try{
			$ar = new Nektria\Recs\MerchantApi\Requests\getAssetsRequest( $this->getOptions() );
			$params = array(
					'version' => Mage::helper('nektria')->getAssetsVersion(), 
					'language' => Mage::app()->getLocale()->getLocaleCode() 
					);
			$this->lastResponse = $ar->execute( $params );
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'assetsRequest ERROR');
			return FALSE;
		}

		$this->log(array($this->lastResponse), 'getAssetsRequest response');		
		
		Mage::getSingleton('checkout/session')->setNektriaJs( $this->lastResponse->getJsUrl() );
		Mage::getSingleton('checkout/session')->setNektriaCss( $this->lastResponse->getCssUrl() );
		Mage::getSingleton('checkout/session')->setNektriaHtml( $this->lastResponse->getHtmlUrl() );

		return TRUE;
	}

	/**
	 * Get the list of covered countries for Nektria
	 * @return array  list of ISO 3166 2 letters codes
	 */
	public function getCoveredCountries(){
		if ($return_value = Mage::getSingleton('checkout/session')->getCoveredCountries( FALSE ) ){
			return $return_value;
		}

		try{
			$cr = new Nektria\Recs\MerchantApi\Requests\CoverageRequest( $this->getOptions() );
			$this->lastResponse = $cr->execute();
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'getCoveredCountries ERROR');
			return FALSE;
		}

		$response = $this->lastResponse->getCoveredCountries();
		$this->log($response, 'getCoveredCountries response');
		Mage::getSingleton('checkout/session')->setCoveredCountries( $response );

		return $response;
	}

	/**
	 * Check if the country code is available for Nektria
	 * @param  string ISO 3166 2 letters codes
	 * @return bool
	 */
	public function checkCoveredCountry($country_code){
		$coveredCountries = $this->getCoveredCountries();

		if ($coveredCountries && in_array($country_code, $coveredCountries)){
			return TRUE;
		}else{
			return FALSE;
		}
	}


	/**
	 * Get the popup Nektria Backend Configuration
	 * @return string url
	 */
	public function getBackendUrl(){
		if ($return_value = Mage::getSingleton('checkout/session')->getNektriaBackendUrl( FALSE ) ){
			return $return_value;
		}

		try{
			$cr = new Nektria\Recs\MerchantApi\Requests\BackendAccessRequest( $this->getOptions() );
			$this->lastResponse = $cr->execute();
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'getBackendUrl ERROR');
			return FALSE;
		}

		$response = $this->lastResponse->getBackendUrl();
		$this->log($response, 'getBackendUrl response');
		Mage::getSingleton('checkout/session')->setNektriaBackendUrl( $response );

		return $response;
	}

	/**
	 * Get the url for Registration popup
	 * @return string url
	 */
	public function getRegistrationUrl(){
		if ($return_value = Mage::getSingleton('checkout/session')->getNektriaRegistrationUrl( FALSE ) ){
			return $return_value;
		}

		try{
			$cr = new Nektria\Recs\MerchantApi\Requests\RegistrationAccessRequest( );
			$this->lastResponse = $cr->execute();
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'getRegistrationUrl ERROR');
			return FALSE;
		}

		$response = $this->lastResponse->getRegistrationUrl();
		$this->log($response, 'getRegistrationUrl response');
		Mage::getSingleton('checkout/session')->setNektriaRegistrationUrl( $response );

		return $response;
	}

	/**
	 * Get an static API Key for Sandbox
	 * @return string apikey
	 */
	function getSandboxApiKey(){
		try{
			$rar = new Nektria\Recs\MerchantApi\Requests\SandboxApiKeyRequest();
			$response = $rar->execute();
			$api_key = $response->getApiKey();
		}catch(Exception $e){
			$this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'getSandboxApiKey ERROR');
			return FALSE;
		}
		
		return $api_key;
	}

	/**
	 * Check the API Key with the service
	 */
	function testRequest(&$returnCode = NULL){
		try {
			$request = new Nektria\Recs\MerchantApi\Requests\TestRequest( $this->getOptions() );
		    $response = $request->execute();
		} catch (Exception $e) {
		    $this->lastError = $e;
			$this->log($e->getCode().$e->getMessage(), 'testRequest ERROR');
			$returnCode = $e->getCode();
			return FALSE;
		}

		return TRUE;
	}

	/* ---------------------------------------------------------- HELPER ----------------------------------------------------------- */

	/**
	 * Helper method for logging, only if dev log is active
	 * @param  $obj Object to pass to logging file
	 * @param  string $msg "Indentificated text"
	 */
	private function log($obj, $msg=''){
		if (! Mage::getStoreConfig('dev/log/active')) 
			return FALSE;
		Mage::log('----------------------'.$msg.'--------------------------', null, $this->logfile);
		Mage::log(var_export($obj, TRUE), null, $this->logfile);
	}
}