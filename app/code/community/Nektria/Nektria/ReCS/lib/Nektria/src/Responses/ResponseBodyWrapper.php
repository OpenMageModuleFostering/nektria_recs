<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

/**
 * Wrapper for all the messages
 * @author mika
 *
 */
class ResponseBodyWrapper
{	
	protected $status;
	private $content;
	private $message;
	private $code;

	public function getStatus(){ return $this->status; }

	public function hasMessage(){ return ! is_null($this->message); }
	public function hasCode(){ return ! is_null($this->code); }
	public function hasContent(){ return !is_null($this->content); }

	public function getMessage(){ return $this->message; }
	public function getCode(){ return $this->code;}


	/**
	 * Instantiate wrapper variables
	 * @param string $body in json format
	 * @throws ApiClientException
	 */
	public function __construct($body)
	{
		if(is_string($body))
			$a_body = json_decode($body, true);
		else 
			$a_body = $body;
		
		if(is_null($a_body))
			throw new ApiClientException("Incorrect Json Format in message response");
		
		if(! array_key_exists("status", $a_body))
			throw new ApiClientException("Status field is compulsory in message response");
		
		//if(! array_key_exists("content", $a_content))
		//	throw new ApiClientException("Content field is compulsory in message response.");
		
		$this->status = $a_body["status"];

		if(array_key_exists("content", $a_body)) // should not be needed...
			$this->content = $a_body["content"];
		
		if(array_key_exists("message", $a_body))
			$this->message = $a_body["message"];
				
		if(array_key_exists("code", $a_body))
			$this->code = $a_body["code"];
	}
	
	public function isSuccessfull(){ return $this->status == "success"; }

	/**
	 * Getter for content, with option for on the fly creation of message object
	 *
	 * @param string $message_class the name of the class interpreting the content
	 * @return mixed
	 */
	public function getContent($message_class=null)
	{
		return is_null($message_class) ?
					$this->content :
					new $message_class($this->content);
	}
}