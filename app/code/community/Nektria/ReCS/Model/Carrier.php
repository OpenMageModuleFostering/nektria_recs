<?php

require_once (Mage::getModuleDir('', 'Nektria_ReCS') . DS . 'lib' . DS .'Nektria.php');

class Nektria_ReCS_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {
	protected $logfile = 'shipping_nektria.log';
	protected $apikey;
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		//check if carrier is enabled
		if (! Mage::helper('nektria')->getEnabled() ) {
			return FALSE;
		}

		//if it's called before returns cached
		if (isset($GLOBALS['lastCollectRates'])){
			return $GLOBALS['lastCollectRates'];
		}

		$this->apikey = Mage::helper('nektria')->getConfig('apikey');
		$this->log($this->apikey, 'apikey');

		$result = Mage::getModel('shipping/rate_result');
		
		//Check availability
			
		//Preparing the products array
		$products = array();
		
		$cart = Mage::getModel('checkout/cart')->getQuote();

		foreach ($cart->getAllVisibleItems() as $item) {
			$producto = array (
					"name" => $item->getProduct()->getName(),
					"reference" => $item->getProduct()->getSku(),
					"quantity" => $item->getQty(),
					"weight_kg" => $item->getProduct()->getWeight()
			);
			$products[] = $producto;
		}
		
		$this->log($products, 'Product list for Nektria');		
		
		//Preparing shipping address data from Magento
		$checkout = Mage::getSingleton('checkout/session')->getQuote();
		
		$shippingAddress = array (
			"postal_code" => $checkout->getShippingAddress()->getPostcode(),
			"street_type" => "",
			"street_name" => $checkout->getShippingAddress()->getStreet(1),
			"street_number" => $checkout->getShippingAddress()->getStreet(2),
			"city" => $checkout->getShippingAddress()->getCity(),
			"country_code" => $checkout->getShippingAddress()->getCountry()
		);
		
		$this->log($shippingAddress, 'Shipping Address sent to Nektria');
		
		//Preparing buyer data
		//If it's a registered user get user data
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$user = array (
				"name" => $customer->getFirstname(),
				"surname" => $customer->getLastname(),
				"email" => $customer->getEmail(),
				"phone" => $checkout->getBillingAddress()->getTelephone()
			);

			$this->log($user, 'User data sent to Nektria');			
		}
		
		//On the other hand, get user data from billing input
		else {
			$user = array (
					"name" => $checkout->getBillingAddress()->getFirstname(),
					"surname" => $checkout->getBillingAddress()->getLastname(),
					"email" => $checkout->getBillingAddress()->getEmail(),
					"phone" => $checkout->getBillingAddress()->getTelephone()
			);
			$this->log($user, 'User data for Nektria');
		}

		$recs = new NektriaSdk();

		//If the country is not available for Nektria, returns empty array
		if (! $recs->checkCoveredCountry($shippingAddress['country_code']) ){
			return array();
		}

		//check Nektria Country availability
		$recs->getCoveredCountries();

		$serviceId = $recs->getServiceId();
		$service_type = $recs->getServiceType();
		$lastPostalCode = $recs->getLastPostalCode();
		$lastShippingAddress = $recs->getLastShippingAddress();

		$this->log($serviceId,'The service ID');
		$this->log($service_type,'The service type');
		$this->log($lastPostalCode,'The lastPostalCode');

		$serviceParams = array( 
					'services' => ['last-mile', 'classic'],
					'shopper' => $user,
					'destination_address' => $shippingAddress,
					'products' => $products
				);
		
		//check if  we have a serviceId, and postal Code and Country code 
		//hasn't been changed in other  case renew serviceId
		$addressChanged = Mage::helper('nektria')->checkChanges($shippingAddress, $lastShippingAddress);

		if ($serviceId && !$addressChanged){
			$this->log(TRUE, 'Inside to KeepAliveRequest');

			$working_service = $recs->keepAlive();

			if ( ! $working_service && $recs->getLastError()->getCode()==400){
				//If we have problems with keepAlive, and serviceId is timeout renew serviceId
				$working_service = $recs->createService( $serviceParams );
				//set Error if costumer doesn't refresh shipping
				$recs->validateSecurity(TRUE);
			}
		}else{
			$this->log(array( $shippingAddress, $lastShippingAddress ), 'No session serviceID, create service');

			//cleans recs session cache
			$recs->clean();
			//create a new service
			$working_service = $recs->createService( $serviceParams );
		}

		//checks first if the nektria service is working in serviceCreation and serviceType after
		$this->log($working_service, 'Working Service' );
		//checks if internal currency code is the same then void transaction
		$currency_code = Mage::app()->getStore()->getBaseCurrencyCode();

		if ($working_service && $shippingAddress['postal_code']=='' && Mage::helper('nektria')->getConfig('lastmiledefault')){
			//If it's first call and only have country, and no postal code then lastmile by default
			$response = $recs->lastMileBestPriceRequest(array(
					'destination_address' => $shippingAddress,
					'products' => $products
				));
			if ($currency_code === $response['currency_code']){
				$result->append($this->_getLastMile($response['best_price']));
			}			
		}else{
			//if web get postal code, then normal process
			if ($working_service && $recs->getServiceType() == 'classic'){
				// Availability - Classic
				if ($addressChanged || ! $shippingAddress['postal_code']){
					//gets request
					$response = $recs->classicAvailabilityRequest();

					if($response->isAvailable() && ( $currency_code === $response->getPrice()->getCurrency())){
						$result->append($this->_getClassic($response->getPrice()->getAmount() ));
					}
				}else{
					//gets the cached response
					$response = $recs->getAvailabilityRequest('classic');

					if( $response && $response['available'] && ($currency_code === $response['currency_code'] )){
						$result->append($this->_getClassic($response['price'] ));
					}
				}			
			}else if($working_service && $recs->getServiceType() !== 'unavailable' ){
				// Availability - Last Mile 
				if (!$addressChanged){
					//gets the cached response
					$response = $recs->getAvailabilityRequest('lastmile');

					if( $response && $response['available'] && ($currency_code === $response['currency_code'] )){
						$lastUserSelection = $recs->getUserSelection();

						if(!$lastUserSelection){
							$result->append($this->_getLastMile( $response['price'] ));
						}else{
							$selectedPrice = Mage::helper('core')->jsonDecode($lastUserSelection);
							$result->append($this->_getLastMile($selectedPrice['total_price']));
						}
					}
				}else{
					//gets new request
					$response = $recs->lastMileAvailabilityRequest();
					$this->log($response->isAvailable(), 'LastMileAvailabilityRequest availability');
					if($response->isAvailable() && ( $currency_code === $response->getBestPrice()->getCurrency()) )
					{
						$lastUserSelection = $recs->getUserSelection();

						if(!$lastUserSelection){
							$result->append($this->_getLastMile( $response->getBestPrice()->getAmount() ));
						}else{
							$selectedPrice = Mage::helper('core')->jsonDecode($lastUserSelection);
							$result->append($this->_getLastMile($selectedPrice['total_price']));
						}			
					}
				}			
			}

		}


			
		
		if ( $working_service )
			$recs->assetsRequest();

		//saves in globals as cache in one request
		$GLOBALS['lastCollectRates'] = $result;
		return $result;
	}
	
	protected function _getLastMile($lm_best_price)
	{
		$this->log($lm_best_price, 'Nektria lastmile best price');

		$rate = Mage::getModel('shipping/rate_result_method');
		/* @var $rate Mage_Shipping_Model_Rate_Result_Method */
	
		$rate->setCarrier(Mage::helper('nektria')->getCode());
		$rate->setCarrierTitle($this->getConfigData('title'));
	
		$methods = $this->getAllowedMethods();

		$rate->setMethod('lastmile');
		$rate->setMethodTitle($methods['lastmile']);
	
		//Set the best price
		$rate->setPrice($lm_best_price);
			
		return $rate;
	}
	
	protected function _getClassic($c_price)
	{
		$rate = Mage::getModel('shipping/rate_result_method');
		/* @var $rate Mage_Shipping_Model_Rate_Result_Method */
		$rate->setCarrier(Mage::helper('nektria')->getCode());
		$rate->setCarrierTitle($this->getConfigData('title'));

		$methods = $this->getAllowedMethods();
		
		$rate->setMethod('classic');
		$rate->setMethodTitle($methods['classic']);
		
		$rate->setPrice($c_price);
		
		return $rate;
	}
	
	public function getAllowedMethods() {
		return Mage::helper('nektria')->getReCSMethods();
	}
	
	
	private function log($obj, $msg=''){
		if (! Mage::getStoreConfig('dev/log/active')) 
			return FALSE;
		Mage::helper('nektria')->log($obj, $msg, $this->logfile);
	}
}