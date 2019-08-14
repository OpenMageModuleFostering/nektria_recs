<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\Exceptions;

class getAssetsResponse
{	
	private $css_url;
	private $js_url;
	private $html_url;
	
	/**
	 * Instanciate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content)
	{
		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;
	
		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in getAssets message response");
	
		if(! array_key_exists("js", $a_content))
			throw new ApiClientException("In field is compulsory in getAssets message response");
	
		if(! array_key_exists("css", $a_content))
			throw new ApiClientException("Out field is compulsory in getAssets message response.");
		
		if(! array_key_exists("html", $a_content))
			throw new ApiClientException("Out field is compulsory in getAssets message response.");
	
		$this->js_url = $a_content["js"];
		$this->css_url = $a_content["css"];
		$this->html_url = $a_content["html"];
	}
	
	public function getCssUrl(){ return $this->css_url; }
	public function getJsUrl(){ return $this->js_url; }
	public function getHtmlUrl(){ return $this->html_url; }
}