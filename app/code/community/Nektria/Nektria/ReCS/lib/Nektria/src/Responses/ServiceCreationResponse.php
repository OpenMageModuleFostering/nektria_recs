<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class ServiceCreationResponse extends BaseResponse
{	
	private $service_number;
	private $service_type;
	
	/**
	 * Instanciate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content)
	{
		$a_content = is_string($content) ? json_decode($content, true) : $content;
		$compulsory = array("service_number", "last-mile", "classic");
		$this->checkCompulsoryFields($compulsory, $a_content);
		
		$this->service_number = $a_content["service_number"];
		$this->service_type = $this->computeServiceType($a_content);
	}
	
	/**
	 * Find out what service we provide.
	 * 
	 * @param array $content
	 * @return string
	 */
	private function computeServiceType(array $content)
	{
		if($content["last-mile"]["availability"])		
			return "last-mile-with-transit";
		else 
			return $content["classic"]["availability"] ? 
						"classic" : 
						"unavailable";		
	}
	
	public function getServiceNumber()
	{
		return $this->service_number;
	}
	
	/**
	 * @return unavailable / classic / last-mile-only / last-mile-with-transit
	 */
	public function getServiceType()
	{
		return $this->service_type;
	}

}