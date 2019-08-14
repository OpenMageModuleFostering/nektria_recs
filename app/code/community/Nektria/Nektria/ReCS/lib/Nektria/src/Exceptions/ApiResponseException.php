<?php

namespace Nektria\Recs\MerchantApi\Exceptions;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class ApiResponseException extends ApiClientException
{
    private $response;

    /**
     *
     * @param string $status_code
     * @param ResponseBodyWrapper $response
     */
    public function __construct($status_code, ResponseBodyWrapper $response)
    {
        parent::__construct($response->getMessage(), $status_code);

        $this->response = $response;
    }

    public function hasApiErrorCode(){ return $this->response->hasCode(); }
    public function hasApiMessageBody(){ return $this->response->hasContent(); }

    public function getApiErrorCode(){ return $this->response->getCode(); }
    public function getApiMessageBody(){ return $this->response->getContent(); }

    /**
     * @return string - fail for 4xx, error for 5xx
     */
    public function getApiStatus(){ return $this->response->getStatus(); }
}