<?php

namespace Nektria\Recs\MerchantApi;

use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class Client
{
    private $api_key;
    private $debug=false;
    private $secure=true;
    private $environment="production";

    /**
     * Settings for base client
     *      timeout - request timeout in seconds, default is 5 seconds
     *      connect_timeout - connection timeout in seconds, default is 3 seconds
     *      exceptions - always set to false (cannot be edited)
     *      other ? we would need to specify this manually in initBaseClientSettings
     *
     * @var array
     */
    private $base_client_settings = ['timeout' => 5, 'connect_timeout' => 3, 'exceptions' => false];

    /**
     * Default settings for service description
     * @var array
     */
    private $service_description_default_settings = [];

    /**
     * Guzzle service description
     * @var \Nektria\Recs\MerchantApi\Description
     */
    private $serviceDescription;

    /**
     * Guzzle base client
     * @var \GuzzleHttp\Client
     */
    private $baseClient;

    /**
     * Api client services
     * @var \GuzzleHttp\Command\Guzzle\GuzzleClient
     */
    private $serviceClient;

    /**
     *  Config settings - for client, among others...
     * 		APIKEY - compulsory
     * 		debug - optional, default value false
     * 		secure - optional, default value no
     * 		environment - optional, default value production
     *      timeout - request timeout in seconds, default is 5 seconds
     *      connect_timeout - connection timeout in seconds, default is 3 seconds
     *
     * @param array $settings Each assigned to the relevant attribute
     * @throws ApiClientException
     */
    public function __construct(array $settings = array())
    {
        if( ! array_key_exists('APIKEY', $settings))
            throw new ApiClientException("APIKEY parameter is needed to use the API");

        $this->api_key = $settings["APIKEY"];

        if(array_key_exists("debug", $settings)){
            $this->debug = $settings["debug"];
        }

        if(array_key_exists("environment", $settings)){
            $this->setEnvironment($settings["environment"]);
        }

        $this->initBaseClientSettings($settings);
        $this->initServiceDescriptionDefaultSettings($settings);
    }

    /**
     * Environment setter - tied to 'secure' attribute
     *      All non development enviroments go over https (secure=true)
     */
    private function setEnvironment($value){
        $this->environment = $value;
        $this->secure = ($value == "development" ? false : true);
        return $this;
    }

    private function setBaseClientSetting($key, $value){
        $this->base_client_settings[$key] = $value;
        return $this;
    }

    private function setServiceDescriptionDefaultSetting($key, $value){
        $this->service_description_default_settings[$key] = $value;
        return $this;
    }

    /**
     * Get settings for the base client
     *      exceptions is always set to false
     * 
     * @return array
     */
    private function getBaseClientSettings(){
        return $this->base_client_settings;
    }

    private function getApiKey(){
        return $this->api_key;
    }

    private function getServiceDescriptionDefaultSettings(){
        return $this->service_description_default_settings;
    }


    /**
     * Extract settings related to base client, init corresponding attribute
     * @param array $settings list of all settings
     */
    private function initBaseClientSettings($settings)
    {
        foreach(['timeout', 'connect_timeout'] as $base_setting_key){
            if(array_key_exists($base_setting_key, $settings)){
                $this->setBaseClientSetting($base_setting_key, $settings[$base_setting_key]);
            }
        }
    }

    /**
     * Filter settings related to service description, init corresponding attribute
     * @param array $settings list of all settings
     */
    private function initServiceDescriptionDefaultSettings($settings){
        $excluded_keys = ["APIKEY", "environment", "timeout", "connect_timeout"];
        $default_settings = array_diff_key ($settings, array_flip($excluded_keys));
        foreach($default_settings as $key => $value){
            $this->setServiceDescriptionDefaultSetting($key, $value);
        }
    }

    public function __call($method, $parameters)
    {
        $this->buildClientIfNeeded();

        return call_user_func_array([$this->serviceClient, $method], $parameters);
    }

    private function buildClientIfNeeded()
    {
        $this->baseClient = new \GuzzleHttp\Client(['defaults' => $this->getBaseClientSettings()]);

        $this->baseClient->setDefaultOption('headers/Authorization', 'Basic '.$this->getApiKey());

        $this->buildServiceDescription();

        $this->serviceClient = new \GuzzleHttp\Command\Guzzle\GuzzleClient(
            $this->baseClient,
            $this->serviceDescription,
            [
                'emitter'  => $this->baseClient->getEmitter(),
                'defaults' => $this->getServiceDescriptionDefaultSettings()
            ]
        );

        if($this->debug===true)
            $this->serviceClient->getEmitter()->attach(new LogSubscriber());
    }

    /**
     * Build base url based on corresponding settings.
     * @return string
     */
    private function buildBaseUrl()
    {
        $protocol = ($this->secure === true) ? "https" : "http";
        switch($this->environment)
        {
            case "development":
                $domain = "localhost:8000";
                break;
            case "sandbox":
                $domain = "recs-staging.herokuapp.com";
                break;
            case "production":
                $domain = "recs.nektria.com";
                break;
            case "timeout":
                $domain = "10.255.255.1";
                break;
            default:
                $domain = "recs.nektria.com";
        }
        return $protocol."://".$domain."/api/";
    }

    /**
     * Build service description
     * Hook on settings to build the base url
     *
     */
    private function buildServiceDescription()
    {
        $apiDescription = $this->getServiceDefinition('services');
        $apiDescription["baseUrl"] = $this->buildBaseUrl();

        $this->serviceDescription = new Description($apiDescription);
    }

    /**
     * Load resource configuration JSON into an array
     * @param string $name - at the moment, always 'services'
     * @return mixed
     */
    private function getServiceDefinition($name)
    {
        $json = file_get_contents(__DIR__.'/services/'.$name.'.json');
        return json_decode($json,true);
    }
}
