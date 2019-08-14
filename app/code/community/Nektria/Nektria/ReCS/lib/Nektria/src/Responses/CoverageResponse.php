<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class CoverageResponse
{
	private $country_codes;

	/**
	 * @return array of country codes
     */
	public function getCoveredCountries()
	{
		return $this->country_codes;
	}

	/**
	 * Instantiate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content=null)
	{
		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;

		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in Coverage message response");

		$this->country_codes = $a_content["country_codes"];
	}
}