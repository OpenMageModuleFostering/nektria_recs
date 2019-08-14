<?php

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
	
		$totals = $cart->getData();
		
		foreach ($cart->getAllVisibleItems() as $item) {
			$prod = $item->getProduct();
			$prodObj = Mage::getModel('catalog/product')->load($prod->getId());
			$prodStockData = Mage::getModel('cataloginventory/stock_item')->loadByProduct($prodObj);
			$prodItem = array (
					'name' => $prod->getName(),
					'reference' => $prod->getSku(),
					'quantity' => $item->getQty(),
					'weight_kg' => $prod->getWeight(),
					'stock' => (int)$prodStockData->getStockQty()
			);
			unset($prod);
			unset($prodStockData);
			unset($prodObj);
			$products[] = $prodItem;
		}
		
		$this->log($products, 'Product list for Nektria');		
		
		//Preparing shipping address data from Magento
		$checkout = Mage::getSingleton('checkout/session')->getQuote();
		
		$shippingAddressObj = $checkout->getShippingAddress();
		$shippingAddress = array (
			'postal_code' => $shippingAddressObj->getPostcode(),
			'street_type' => '',
			'street_name' => $shippingAddressObj->getStreet(1),
			'street_number' => $shippingAddressObj->getStreet(2),
			'city' => $shippingAddressObj->getCity(),
			'country_code' => $shippingAddressObj->getCountry()
		);
		$this->log($products, 'Shipping Address sent to Nektria OBJ');
		unset($shippingAddressObj);
		
		$this->log($shippingAddress, 'Shipping Address sent to Nektria');
		
		//Preparing buyer data
		//If it's a registered user get user data
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$user = array (
				'name' => $customer->getFirstname(),
				'surname' => $customer->getLastname(),
				'email' => $customer->getEmail(),
				'phone' => $checkout->getBillingAddress()->getTelephone()
			);

			$this->log($user, 'User data sent to Nektria');			
		}
		
		//On the other hand, get user data from billing input
		else {
			$user = array (
					'name' => $checkout->getBillingAddress()->getFirstname(),
					'surname' => $checkout->getBillingAddress()->getLastname(),
					'email' => $checkout->getBillingAddress()->getEmail(),
					'phone' => $checkout->getBillingAddress()->getTelephone()
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
		$lastSubtotal = $recs->getLastSubtotal();

		$this->log($lastSubtotal, 'get last subtotal');


		$this->log($serviceId,'The service ID');
		$this->log($service_type,'The service type');
		$this->log($lastPostalCode,'The lastPostalCode');

		//checks if internal currency code is the same then void transaction
		$currency_code = Mage::app()->getStore()->getBaseCurrencyCode();

		$serviceParams = array( 
					'services' => ['last-mile', 'classic'],
					'total_price' => $totals['base_subtotal_with_discount'],
					//'total_price' => $request->getBaseSubtotalWithDiscount(),
					'shopper' => $user,
					'destination_address' => $shippingAddress,
					'products' => $products,
					//not used yet, ignored in API
					'currency_code' => $currency_code
				);
		
		//check if  we have a serviceId, and postal Code and Country code 
		//hasn't been changed in other  case renew serviceId
		$addressChanged = Mage::helper('nektria')->checkChanges($shippingAddress, $lastShippingAddress);
		$subtotalChanged = Mage::helper('nektria')->checkChanges($totals['base_subtotal_with_discount'], $lastSubtotal);

		if ($serviceId && !$addressChanged && !$subtotalChanged){
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
		if($working_service === FALSE){
			$this->log($working_service, 'NO Working Service' );
			return FALSE;
		}
		$this->log($working_service, 'Working Service' );

		if ($working_service && $shippingAddress['postal_code']=='' && Mage::helper('nektria')->getConfig('lastmiledefault')){
			//If it's first call and only have country, and no postal code then lastmile by default
			$response = $recs->lastMileBestPriceRequest(array(
					'currency_code' => $serviceParams['currency_code'],
					'total_price' => $serviceParams['total_price'],
					//Not used
					//'destination_address' => $shippingAddress,
					//'products' => $products
				));

			if ($response === FALSE){
				$this->log($response, 'No valid lastMileBestPriceRequest response' );
				return FALSE;
			}

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

					if ($response === FALSE){
						$this->log($response, 'No valid classicAvailabilityRequest response' );
						return FALSE;
					}

					if($response && $response->isAvailable() && ( $currency_code === $response->getPrice()->getCurrency())){
						$result->append($this->_getClassic($response->getPrice()->getAmount() ));
					}else if($currency_code != $response->getPrice()->getCurrency()){
						$this->log($currency_code, 'Merchand currency code is different to currency response' );
					}
				}else{
					//gets the cached response
					$response = $recs->getAvailabilityRequest('classic');

					if( $response && $response['available'] && ($currency_code === $response['currency_code'] )){
						$result->append($this->_getClassic($response['price'] ));
					}else if($currency_code != $response['currency_code']){
						$this->log($currency_code, 'Merchand currency code is different to currency response' );
					}
				}			
			}else if($working_service && $recs->getServiceType() !== 'unavailable' ){

				// Availability - Last Mile 
				if (!$addressChanged && !$subtotalChanged){

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
					}else if($currency_code != $response['currency_code']){
						$this->log($currency_code, 'Merchand currency code is different to currency response' );
					}
				}else{
					//gets new request
					$response = $recs->lastMileAvailabilityRequest();
					if ($response === FALSE){
						$this->log($response, 'No valid lastMileAvailabilityRequest response' );
						return FALSE;
					}

					$this->log($response->isAvailable(), 'LastMileAvailabilityRequest availability');
					if($response && $response->isAvailable() && ( $currency_code === $response->getBestPrice()->getCurrency()) )
					{
						$lastUserSelection = $recs->getUserSelection();

						if(!$lastUserSelection){

							$result->append($this->_getLastMile( $response->getBestPrice()->getAmount() ));
						}else{

							$selectedPrice = Mage::helper('core')->jsonDecode($lastUserSelection);
							$result->append($this->_getLastMile($selectedPrice['total_price']));
						}			
					}else if($currency_code != $response->getBestPrice()->getCurrency()){
						$this->log($currency_code, 'Merchand currency code is different to currency response' );
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