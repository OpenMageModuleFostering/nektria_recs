<?php

// Require the Composer autoloader.
require 'vendor/autoload.php';

function executeRequest($request_name)
{	
	switch($request_name)
	{
		case "test":
			$tr = new Nektria\Recs\MerchantApi\Requests\TestRequest([
					'APIKEY' => 'dGVzdDp0ZXN0'
			]);			
			$response = $tr->execute();
		break;
		case "service-creation": 
			$response = executeServiceCreation();
		break;
		case "last-mile-availability":			  
			$response = executeLastMileAvailability();
		break;
		case "last-mile-validation":
			$response = executeLastMileValidation();
		break;
		case "last-mile-confirmation":
			$lmcr = new Nektria\Recs\MerchantApi\Requests\LastMileConfirmationRequest([
					'APIKEY' => 'dGVzdDp0ZXN0',
					'id'	 => '1'
			]);
				
			$response = $lmcr->execute();
		break;
		case "classic-availability":
			$response = executeClassicAvailability();
		break;
		case "classic-confirmation":
			$lmcr = new Nektria\Recs\MerchantApi\Requests\ClassicConfirmationRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'
					]);
					
			$response = $lmcr->execute();
		break;
		case "keep-alive":
			$kar = new Nektria\Recs\MerchantApi\Requests\KeepAliveRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'
					]);
		
			$more_params = array("here" => "is", "some" => "more");
			$response = $kar->execute($more_params);
		break;
		case "get-assets":
			$gar = new Nektria\Recs\MerchantApi\Requests\getAssetsRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'
					]);
		
			$more_params = array("here" => "is", "some" => "more");
			$response = $gar->execute($more_params);
		break;
		default:
			$response = "Incorrect parameter $request_name. This is not a know service.";
	}
	
	return $response;
}

$opt = getopt("r:");

if(array_key_exists("r", $opt))
{
	var_dump(executeRequest($opt["r"]));
}
else
{
	echo 'use: test.php [-r test-name]
	with test name in 
	"test", "service-creation", "last-mile-availability", 
	"last-mile-validation", "last-mile-confirmation", 
	"classic-availability", "classic-confirmation", 
	"keep-alive", "get-assets"
';
}



function executeServiceCreation()
{
	$sr = new Nektria\Recs\MerchantApi\Requests\ServiceCreationRequest([
			'APIKEY' => 'dGVzdDp0ZXN0'
	]);
	
	$params = array(
			"order_number" => "abc",
			"services" => ["last-mile", "classic"],
			"shopper" => array(
				"name" => "Roberto",
				"surname" => "Rodríguez",
				"email" => "roberto.rodriguez@gmail.com",
				"phone" => "83486923409"
			),
			"destination_address" => array(
					"postal_code" => "08022",
					"street_type" => "Pza.",
					"street_name" => "Urquinaona",
					"street_number" => "5",
					"city" => "Barcelona",
					"country_code" => "ES"
			),
			"products" => array(
					
					array(
						"name" => "T-shirt Monashee",
						"reference" => "PQR48-D",
						"quantity" => 1,								
						"weight_kg" => 0.5,
						"size" =>
							array(
								"height_cm" => 20,
								"width_cm" => 10,
								"depth_cm" =>10
							)
					),
					array(
						"name" => "Jeans Lemon pie",
						"reference" => "WDV48-D",
						"quantity" => 2,
						"weight_kg" => 0.5,
						"size" =>
							array(
									"height_cm" => 20,
									"width_cm" => 10,
									"depth_cm" =>10
							)
					)
			)
	);
	
	return $sr->execute($params);
}

function executeLastMileAvailability()
{
	$lmar = new Nektria\Recs\MerchantApi\Requests\LastMileAvailabilityRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'			
	]);
		
	$more_params = array("service_type" => "last-mile-with-transit");
	return $lmar->execute($more_params);
	
}

function executeLastMileValidation()
{	
	$lmvr = new Nektria\Recs\MerchantApi\Requests\LastMileValidationRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'
	]);
	
	$params = array(			
			"delivery_windows" => [["start_time" => "2015-05-19T14:00:00+02:00", "end_time" => "2015-05-19T17:00:00+02:00"]],
			"validation_windows" => [
							["start_time" => "2015-05-18T12:00:00+02:00", "end_time" => "2015-05-18T14:00:00+02:00"],
							["start_time" => "2015-05-18T16:00:00+02:00", "end_time" => "2015-05-18T18:00:00+02:00"]
			],
			"total_price" => 16.3,
			"currency_code" => "EUR"					
	);
	try 
	{	
		return $lmvr->execute($params);
	}	
	catch(GuzzleHttp\Command\Exception\CommandException $e)
	{
		var_dump($e->getResponse()->getBody()->getContents());
		var_dump($e->getResponse()->getStatusCode());
	
	}
	catch(GuzzleHttp\Command\Exception\CommandClientException $e)
	{
		var_dump($e->getResponse()->getBody()->getContents());
		var_dump($e->getResponse()->getStatusCode());
		var_dump($e->getResponse()->getEffectiveUrl());
	}
	catch(GuzzleHttp\Command\Exception\CommandServerException $e)
	{
		var_dump($e->getResponse()->getBody()->getContents());
		var_dump($e->getResponse()->getStatusCode());
		var_dump($e->getResponse()->getEffectiveUrl());
	}
	catch(GuzzleHttp\Command\Exception\CommandException $e)
	{
		var_dump(get_class($e));
	}
}

function executeClassicAvailability()
{
	$car = new Nektria\Recs\MerchantApi\Requests\ClassicAvailabilityRequest([
			'APIKEY' => 'dGVzdDp0ZXN0',
			'id'	 => '1'
	]);

	$more_params = array("service_type" => "classic");
	return $car->execute($more_params);

}

