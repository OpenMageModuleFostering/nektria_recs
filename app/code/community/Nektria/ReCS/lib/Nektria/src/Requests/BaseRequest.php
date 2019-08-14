<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Client;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

/**
 * Base request deals with client creation
 * 
 * @author mika
 *
 */
abstract class BaseRequest
{	
	protected $client;
	
	private $request_keys;
	protected $request_settings;
	
	/**
	 * Split settings into client settings and request settings
	 * @param array $settings
	 */
	public function __construct(array $settings = array())
	{
		$this->request_keys = array("id"); // more to come?
		list($client_settings, $request_settings) = $this->split($settings);
				
		$this->request_settings = $request_settings;
		$this->client = new Client($client_settings);
	}
	
	/**
	 * Separate keys related to client settings from keys related to the request
	 * 
	 * @param array $settings
	 * @return multitype: array split into an array of settings and an array 
	 * 			of request settings
	 */
	private function split(array $settings)
	{
		$request_keys = $this->request_keys;
		$request_settings = array();
		foreach($request_keys as $rq)
		{
			if( ! array_key_exists($rq, $settings)) continue;
			$request_settings[$rq] = $settings[$rq];
			unset($settings[$rq]);
		}
		return array($settings, $request_settings);
			
	}
	
	protected function mergeRequestSettings($params)
	{
		return array_merge($params, $this->request_settings);
	}
	
	protected function getRequestSettings(){ return $this->request_settings; }
	
	/**
	 * Shoot the request, get response
	 * @param $params string or array of parameters in the body
	 * @return BaseResponse
	 */
	abstract protected function unsafe_execute(array $params);
	
	/**
	 * Get to execute specific workload catching Guzzle exception and converting them to 
	 * more readable exceptions of our flavour.
	 *
	 * 			OBSOLETE as we exectue guzzle with option to throw no exception.
	 * 
	 * @param string $params json parame ters
	 * @return \Nektria\Recs\MerchantApi\Responses\BaseResponse
	 * @throws ApiClientException
	 */
	public function execute($params=array())
	{
		if(! is_array($params))
			$params = json_decode($params, true);

		if(! is_array($params))
			throw new ApiClientException("Invalid parameter format in the request execution call.");

		try
		{
			return $this->unsafe_execute($params);
		}
		catch(GuzzleHttp\Command\Exception\CommandClientException $e)
		{
			$message = "Error ".$e->getResponse()->getStatusCode().":".$e->getResponse()->getBody()->getContents();
			// var_dump($e->getResponse()->getEffectiveUrl());
			throw new ApiClientException($message, $e->getResponse()->getStatusCode());
		}
		catch(GuzzleHttp\Command\Exception\CommandServerException $e)
		{
			$message = "Error ".$e->getResponse()->getStatusCode().":".$e->getResponse()->getBody()->getContents();
			// var_dump($e->getResponse()->getEffectiveUrl());
			throw new ApiClientException($message, $e->getResponse()->getStatusCode());
		}
		catch(GuzzleHttp\Command\Exception\CommandException $e)		
		{
			$message = "Error ".$e->getResponse()->getStatusCode().":".$e->getResponse()->getBody()->getContents();
			throw new ApiClientException($message, $e->getResponse()->getStatusCode());
		}
		catch(GuzzleHttp\Exception\RequestException $e)
		{
			error_log($e->getResponse()->getContent());
			var_dump($e->getResponse()->getContent());
			throw($e);
		}
		catch (GuzzleHttp\Exception\BadResponseException $e) 
		{
			error_log($e->getResponse()->getContent());
    		var_dump($e->getResponse()->getContent());
    		throw($e);
		}
		catch (GuzzleHttp\Exception\ServerException $e)
		{
			error_log($e->getResponse()->getContent());
			var_dump($e->getResponse()->getContent());
			throw($e);
		}
	}
}