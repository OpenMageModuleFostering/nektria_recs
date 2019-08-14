##Merchant API Client Library for PHP

This is a Software Development Kit for Merchants that want to use Nektria Responsive eCommerce Shipping Services. After explaining the basics of the API, we provide the full integration workflow that needs to be followed to propose Nektria Delivery Service to the eCommerce shoppers.


## Test and Configuration

A test request is made available to verify the connection to the SDK for a given API key. All requests implement the exectute method which will throw an ApiClientException if the API returns an error response (status code 4xx and 5xx).

### Create and Run a Test Request

```
$request = new Nektria\Recs\MerchantApi\Requests\TestRequest([
		'APIKEY' => 'dGVzdDp0ZXN0'
]);

// Execute the 'test' method to check the connection with the API
try {
    $response = $request->execute();
} catch (ApiClientException $e) {
    echo "There was an error connecting with the API : ".$e->getMessage();
}

```

### Debug mode

example

```
$recsClient = new Nektria\Recs\MerchantApi\Client([
	'debug' => true	
]);
```
Default value is false.

### Environment and Sandbox


All requests are run against the live environment. In order to make requests that will not result in real orders, you can use the sandbox service using the following parameter:

```
$recsClient = new Nektria\Recs\MerchantApi\Client([
	'environment' => 'sandbox'
]);
```
Default value is 'production'.

### Request Timeouts

The `timeout` and `connect\_timeout` options set the maximum time API requests will wait for the connection or the reply. 
The default values are 3 seconds for connection, and 5 seconds for a reply.

```
$recsClient = new Nektria\Recs\MerchantApi\Client([
	'timeout' => 2.25,
	'connect_timeout' => 1.5
]);
```

### More Request and Response examples

Here is a service creation request run against the sandbox with debug information.

```
$sr = new Nektria\Recs\MerchantApi\Requests\ServiceCreationRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'environment' => 'sandbox',
	'debug' => true	
]);

$response = $sr->execute([
	"session_timeout" => 600,
	"currency_code" => "EUR",
	"shopper" => [
		"name" => "Roberto",
		"surname" => "Rodríguez",
		"email" => "roberto.rodriguez@gmail.com",
		"phone" => "83486923409"
	],
	"destination_address" => [
		"postal_code" => "08022",
		"street_name" => "Urquinaona",
		"street_number" => "5",
		"city" => "Barcelona",
		"country_code" => "ES"
    ],
	"products" => [
		[
			"name" => "T-shirt Monashee",
			"reference" => "PQR48-D",
			"quantity" => 1,
			"weight_kg" => 0.5
		],
		[
			"name" => "Jeans Lemon pie",
			"reference" => "WDV48-D",
			"quantity" => 2,
			"weight_kg" => 0.5
		]
	]
]);
```

or a last mile availability request run in production

```
$lmar = new Nektria\Recs\MerchantApi\Requests\LastMileAvailabilityRequest([
		'APIKEY' => 'dGVzdDp0ZXN0',
		'id'	 => '1234'
]);

$request_body_params = array("service_type" => "last-mile-only");
$response = $lmar->execute($request_body_params);
```

### Exceptions

All SDK exceptions are children of the base exception Nektria\Recs\MerchantApi\Exceptions\ApiClientException.

Any request that receives an error response when executed (http status code 4xx or 5xx) throws a Nektria\Recs\MerchantApi\Exceptions\ApiResponseException. This exception provides the following methods:

- exact http status code: __getCode__ method
- api specific error code: __getApiErrorCode__ method.
- error message: __getMessage__ method 
- (sometimes) details about the error: __getApiMessageBody__ method. 

Here comes a list of API specific error codes, along with the corresponding http error code:

- \#1.	Formato de mensaje incorrecto. (HTTP 400 Bad Request)
- \#2.	ContentType no válido. (HTTP 415 Unsupported Media Type)
- \#3.	Falta cuerpo del mensaje. (HTTP 400 Bad Request)
- \#4.	El usuario o la contraseña no son correctos. (HTTP 401 Unauthorized)
- \#5.	No puede acceder a este recurso. (HTTP 403 Forbidden)
- \#6.	El método que se quiere aplicar a este recurso ha expirado (http 400 Bad Request)
- \#7.	El formato o los datos de la petición son incorrectos. (HTTP 422 Unprocesable Entity)
- \#20.	Código de moneda incorrecto (HTTP 422 Unprocesable Entity)
- \#21.	Código de moneda no soportado (HTTP 422 Unprocesable Entity)
- \#22.	Código de país incorrecto (HTTP 422 Unprocesable Entity)
- \#23.	Código de país no soportado (HTTP 422 Unprocesable Entity)
- \#24.	Código Postal incorrecto (HTTP 422 Unprocesable Entity)
- \#25.	Ventana horaria incorrecta (HTTP 422 Unprocesable Entity)

## Workflow for merchant modules

Here follows a list of steps to integrate the module with a user checkout process.

### Initialize carriers availabilities

0) Find price to be displayed for the last mile option (whenever we need to display this price before we know shopper's address)
1) Setup the service, get service number and service type (_HOOK\_GATHER\_CARRIERS_)<br/>
2) Hook the classic delivery carrier module<br/>
3) Hook the last mile delivery carrier module

```
// 0) Last Mile Price
$scr = new Nektria\Recs\MerchantApi\Requests\LastMileBestPriceRequest([
	'APIKEY' => 'dGVzdDp0ZXN0'
]);

$response = $scr->execute([
					"destination_address" => $basket->getShopperAddress(),
					"products" => $basket->getProductsDetails()
]);

$best_price = $response->getPrice();
```
...

```
// 1) Service Setup
$scr = new Nektria\Recs\MerchantApi\Requests\ServiceCreationRequest([
	'APIKEY' => 'dGVzdDp0ZXN0'
]);

$response = $scr->execute([
					"session_timeout" => 600,
                    "currency_code" => "EUR",
					"shopper" => $basket->getShopperDetails(),
					"destination_address" => $basket->getShopperAddress(),
					"products" => $basket->getProductsDetails()
]);

$id = $response->getServiceNumber();
$service_type = $response->getServiceType();
```
...

```
// 2) Classic carrier hook
$car = new Nektria\Recs\MerchantApi\Requests\ClassicAvailabilityRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $car->execute(array(
					"service_type" => $service_type
));

if($response->isAvailable()) 
	your_display_classic_carrier($response->getPrice());
```
...

```

// 3) Last Mile carrier hook
$lmar = new Nektria\Recs\MerchantApi\Requests\LastMileAvailabilityRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $lmar->execute(array(
					"service_type" => $service_type
));

if($response->isAvailable()) 
{
	your_display_last_mile_carrier($response->getBestPrice());
	$price_matrix = $response->getPriceMatrix();
	your_store_string_into_session($price_matrix);
}

```

### Initialize last mile widget

This needs to be done only if the last mile availability request has returned some availability. There is no harm if it is always done, however, since it will stay hidden.

4) Get the list of resources (_HOOK\_SHOW\_LM\_CARRIER_), specifying language settings and  version number. At the moment, the only version version we manage is '1', and the only 
language is 'es_ES'.<br/>
5) Place the resources in the layout

- a) js
- b) css
- c) html 

6) Setup javascript to manage widget information:

- a) Setup the selection of last mile carrier to trigger the javascript method `nektria_recs.showTimeWindowArea()`<br/>
- b) Setup the unselection of last mile carrier to trigger the javascript method `nektria_recs.hideTimeWindowArea()`<br/>
- c) Setup callback function:

	***It performs the following tasks***
	
     * Update shopping cart with transportation information provided
     * Update total cost corresponding to selected option

	***And takes two parameters***
	
     * @param string `user_calendar_selection` json that needs to be stored and passed to last mile validation request
     * @param float `total_price` price of the selected transportation option

- d) Once the widget html has been loaded, call javascript method that initializes available windows `nektria_recs.initTimeWindowPrices(<?php echo $price_matrix; ?>, callback_function);`

The Controller could go like this

```
// 4) After carriers are loaded, get resources
$ar = new Nektria\Recs\MerchantApi\Requests\getAssetsRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id,
]);

$response = $ar->execute(array('version' => 1, 'language' => 'es_ES'));
$css_url  = $response->getCssUrl();
$js_url   = $response->getJsUrl();
$html_url = $response->getHtmlUrl();
```

Here is how it may look like in the Template

```
	<head>
	....
	<!-- 5a) place js -->
	<script src="<?php print $js_url; ?>"></script>
	<!-- 5b) place css -->
	<link rel="stylesheet" href="<?php print $css_url; ?>" />

	<!-- 6c) Create callback function -->
	<script>    
	    your_cbfunction_8473028 = function(user_calendar_selection, total_price)
    	{                       
			// Add to the basket: user_calendar_selection;
			(...) 
			// update_total_price
			(...)
		}	
	</script>
```
....

```
	</head>
	<body>

          <li>    
             <!-- 6a) show price grid when last mile selected -->
	          <input name="shipping_method" type="radio" value="nektria" id="s_method_matrixrate_matrixrate_10046" class="radio validate-one-required-by-name" onclick="nektria_recs.showTimeWindowArea()">
   	       <label for="s_method_matrixrate_matrixrate_10046">ReCS                                                                        			<span class="price">8,16 €</span>                                                			</label>
          </li>
		<li>
			<!-- 6b) hide price grid when last mile selected -->
       	   <input name="shipping_method" type="radio" value="nacex" id="s_method_matrixrate_matrixrate_10041" class="radio" onclick="nektria_recs.hideTimeWindowArea()">
          	<label for="s_method_matrixrate_matrixrate_10041">NACEX                                                                        <span class="price">8,12 €</span>                                                </label>
          </li>     
```     
....

```
	<!-- 5c) place html -->
	<?php print file_get_contents($html_url); ?>
....
</body>

```     
....

```
	<!-- 6d) Init widget -->

	<script type="text/javascript">        
    	nektria_recs.initTimeWindowPrices(<?php print $price_matrix; ?>, your_cbfunction_8473028);
	</script>

```

7) If the page is being reloaded and a selection was done previously, call the `nektria_recs.updateSelectedWindows(user_calendar_selection)` method to 
 make sure the calendar appears with correctly selected cells. The parameter `user_calendar_selection` is the one that was passed by the widget to the callback function.

### If classic carrier is chosen by the shopper

8) Confirm Classic Carrier after the user has made the payment (_HOOK\_ORDER\_CONFIRMED_).

```
// 8) Order Confirmed hook
$ccr = new Nektria\Recs\MerchantApi\Requests\ClassicConfirmationRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $ccr->execute(array("order_number" => $basket->getOrderNumber()));
```

__And your are done !__

### If last mile carrier is chosen by the shopper

8) !! Make sure the price provided in the user selection for the API matches the price in the shopper basket  
9) Validate last mile request with information retrieved from final selection.<br/>
10) Just before the payment, refresh the shipping order. (_HOOK\_PRE\_PAYMENT_)<br/>
11) After the payment, confirm the shipping order. (_HOOK\_ORDER\_CONFRIMED_)


```
// 8) Check basket shipping price matches the one provided to the API
$user_selection = your_get_user_calendar_selection_from_the_basket();
$a = json_decode($user_selection ,true);
$shipping_price = $a["total_price"];
basket_shipping_price = your_pickup_basket_shipping_price();
if($shipping_price != $basket_shipping_price)
	throw new BookingException("Price mismatch - the price in the basket is incorrect");

// 9) When carrier is chosen, validate the shipping order
$lmvr = new Nektria\Recs\MerchantApi\Requests\LastMileValidationRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $lmvr->execute($user_selection);
```
...

```
// 10) Before payment, make sure shipping order does not expire
$kar = new Nektria\Recs\MerchantApi\Requests\KeepAliveRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $kar->execute();
```
...

```
// 11) When order is confirmed, confirm shipping
$lmcr = new Nektria\Recs\MerchantApi\Requests\LastMileConfirmationRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $lmcr->execute(array(
					"order_number" => $basket->getOrderNumber()
			));
```

__And your are done !__

## eCommerce Backend

### Configuration

In the eCommerce config panel, we will see the following options:

- api key, plus registration button if no API Key is available
- sandbox mode

If the API key is left empty, when we are using the Sandbox mode, a default key is assigned automatically by the SDK. In
 that way, the shipping module can be used to perform tests out of the box, with no need to go through the registration 
 process. On the other hand, a requests makes it possible to retrieve a valid API Key for testing purposes. This is done 
 via the SandboxApiKeyRequest, which goes as follow:
 
 ```
 // Module configuration controller
 $rar = new Nektria\Recs\MerchantApi\Requests\SandboxApiKeyRequest(
 	// no API key is needed
 );
 
 $response = $rar->execute();
 			
 $api_key = $response->getApiKey();
 ```

### Registration link

The registration link must be made available next to the api key field: with a label "Sing up or Log In", the link will 
open a full window popup to a page where the user will be able to create an account. If he is already registered, he will
 be able to access a control panel with his credentials.

```
// Module configuration controller
$rar = new Nektria\Recs\MerchantApi\Requests\RegistrationAccessRequest(
	// no API key is needed
);

$response = $rar->execute();
			
$url = $response->getRegistrationUrl();
```

then in the view we may have something like this (new tab trick version)

```
<!-- Module configuration view -->
<script type="text/javascript">
function OpenInNewTab(url) {
  var win = window.open(url, '_blank');
  win.focus();
}
</script>
<div onclick='OpenInNewTab("<?php echo $url ?>");'>Sign up or Log In</div>
```

### Advanced Configuration

Advanced configuration options are made available on a specific webpage. This url will be retrieved calling the getBackendUrl
method of a BackendAccessResponse object. It will be made available to the merchant ideally via an iframe in a modal window, 
though a simple popup would cover the needs. This url will have a timeout, ideally bigger than the  session timeout of 
Magento backend.

```
// Module configuration controller
$br = new Nektria\Recs\MerchantApi\Requests\BackendAccessRequest([
	'APIKEY' => 'dGVzdDp0ZXN0'
]);

$response = $br->execute();
			
$url = $response->getBackendUrl();
```

then in the view we may have something like this (popup version)

```
	<!-- Module configuration view -->
	<a href="<?php echo $url ?>" target="_blank" onClick="window.open(this.href, this.target, 'width=800,height=600'); return false;">
	Advanced Configuration
	onboiarding&co cs</a>
```

### Shipping information

Information about a given shipping can be displayed using the ShowShippingRequest object, which will generate a ShowShippingResponse. The ShowShippingHelper provides methods to display this response in the backend.
Note: if a shipping has no last mile, then nothing should be shown.

```
// Show Shipping Controller
$ccr = new Nektria\Recs\MerchantApi\Requests\ShowShippingRequest([
	'APIKEY' => 'dGVzdDp0ZXN0',
	'id' => $id
]);

$response = $ccr->execute();
$recs_shipping_info = new Nektria\Recs\MerchantApi\ShowShippingHelper($response, $locale);
```

```
// Show Shipping View
<? if($recs_shipping_info->hasLastMile())
   {
     echo "Estado del envío: ".$recs_shipping_info->getStatus()."<br/>";
     echo "Franjas horarias de entrega: "     
     foreach($recs_shipping_info->getDeliveryWindows() as $window)
     	echo $window."<br/>";
   }
]);

$response = $ccr->execute();
```

### List of countries where we have operations

You can get a list of the countries where we have operations. This will come as a list of ISO 3166 2 letters codes when
 running the getCoveredCountries method of the CoverageResponse object, obtained as follow 

```
// Module configuration controller
$cr = new Nektria\Recs\MerchantApi\Requests\CoverageRequest([
	'APIKEY' => 'dGVzdDp0ZXN0'
]);

$response = $cr->execute();
			
$countries = $response->getCoveredCountries();
```

## Data Formats used in the requests


### Addresses

The addresses will be used to identify where to deliver the products. They are structured as follow:

- postal_code
	- Type: String
	- Required: Yes
	- Description: postal code
- street_type
	- Type: String
	- Required: No
	- Description: street type (street, place, etc.)
- street_name
	- Type: String
	- Required: Yes
	- Description: street name.
- street_number
	- Type: Integer
	- Required: No
	- Description: street number
- floor
	- Type: String
	- Required: No
	- Description: floor
- door
	- Type: String
	- Required: No
	- Description: door
- city
	- Type: String
	- Required: Yes
	- Description: city name
- province
	- Type: String
	- Required: No
	- Description: province
- country_code
	- Type: CountryCode
	- Required: Yes
	- Description: 2 letter ISO3166 code of the country.

### Shoppers

Shopper information is used to identify the shopper and provides contact information.

- name
	- Type: String
	- Required: Yes
	- Description: name
- surname
	- Type: String
	- Required: Yes
	- Description: surnames.
- email
	- Type: String
	- Required: Yes
	- Description: email
- phone
	- Type: String
	- Required: No
	- Description: phone number

### Products

Products are identified by their type, their availability, price and size.

- name
	- Type: String
	- Required: Yes
	- Description: product name
- reference
	- Type: String
	- Required: Yes
	- Description: product reference in eCommerce.
- quantity
	- Type: Integer
	- Required: Yes
	- Description: number of products of this type in the basket
- Size
	- Type: SizeType
	- Required: No
	- Description: product size
- weight_kg
	- Type: Float
	- Required: No
	- Description: peso de un producto en kg - origin_information
- price
	- Type: Price (float with 2 decimal digits maximum)
	- Required: No
	- Description: price of one product.
- currency_code
	- Type: Currency Code
	- Required: No
	- Description: currency code as defined in ISO4217. For now, we will only accept transactions in euros ("EUR")

### Size

Product Size is defined as follow:

- height_cm
	- Type: Integer
	- Required: Yes
	- Description: height in cm
- width_cm
	- Type: Integer
	- Required: Yes
	- Description: width in cm
- depth_cm
	- Type: Integer
	- Required: Yes
	- Description: depth in cm
	

## Appendix - Under the hood

All the following method calls are used in the Nektria\Recs\MerchantApi\Requests\BaseRequest class. All SDK requests inherit from BaseRequest, and use transparently these methods, transmitting all the parameters that are passed in the constructor.
 
### Create the ReCS API client

```
<?php
// Require the Composer autoloader.
require 'vendor/autoload.php';

// Instantiate an ReCS client using your API KEY.
$recsClient = new Nektria\Recs\MerchantApi\Client([
	'APIKEY' => 'my-own-api-key'
]);
```

### Run a configured request

For example, the test request.

```
<?php
// Execute the 'test' method to check the connection with the API
try {
    $recsClient->test();
} catch (Exception $e) {
    echo "There was an error connecting with the API : ".$e->getMessage();
}
```
