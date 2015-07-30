<?php

namespace OClient\Service;

use Zend\Http\Client;
use Zend\Http\Request;

class BaseService {

    /**
     *
     * @var array 
     */
    protected $apiConfiguration;

    public function __construct() {
        $this->apiConfiguration = array();
    }

    /**
     * 
     * @param string $api_base_url
     * @param string $api_key
     * @return \OClient\Service\Product\PictureRetriever
     */
    public function setApiConfiguration($api_base_url, $api_key) {
        // Clean up eventual ending slashes.
        $api_base_url = preg_replace('/(\/)+$/', '', $api_base_url);
        $this->apiConfiguration = array();
        $this->apiConfiguration['base_url'] = $api_base_url;
        $this->apiConfiguration['api_key'] = $api_key;
        return $this;
    }

    /**
     * 
     * @return string
     * @throws \RuntimeException
     */
    protected function getApiBaseUrl() {
        if (!array_key_exists('base_url', $this->apiConfiguration)) {
            throw new \RuntimeException("ApiConfiguration must be set prior to use service. Use setApiConfiguration() before using service.");
        }
        if ($this->apiConfiguration['base_url'] == '') {
            throw new \RuntimeException("API base_url cannot be emtpy, check your setup.");
        }
        return $this->apiConfiguration['base_url'];
    }

    /**
     * 
     * @return string
     * @throws \RuntimeException
     */
    protected function getApiKey() {
        if (!array_key_exists('api_key', $this->apiConfiguration)) {
            throw new \RuntimeException("ApiConfiguration must be set prior to use service. Use setApiConfiguration() before using service.");
        }
        if ($this->apiConfiguration['api_key'] == '') {
            throw new \RuntimeException("API key cannot be emtpy, check your setup.");
        }
        return $this->apiConfiguration['api_key'];
    }

    /**
     * 
     * @param string $uri
     * @return \Zend\Http\Client
     */
    protected function getHttpClient($uri) {
        $client = new Client();
        $client->setUri($uri);
        $client->setOptions(array(
            'maxredirects' => 5,
            'timeout' => 30
        ))->setHeaders(array(
            //'Contet-type' => 'text/plain',
            'Accept-encoding' => 'gzip,deflate',
            //'Vary' => 'UTF-8',
            'X-Powered-By: OClient'
        ));
        return $client;
    }

    protected function getClientUrl(Client $client) {
        $uri = $client->getUri();
        $query = $client->getRequest()->getQuery()->toString();
        //$post = $client->getRequest()->getPost()->toString();
        return $uri . '?' . $query;
    }

    /**
     * Execute a remote request (http) and return content
     * 
     * @param string $uri
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    protected function retrieve($uri, $parameters = array()) {
        ini_set('default_charset', 'UTF-8');

        $client = $this->getHttpClient($uri);
        //$client->setEncType(Client::ENC_FORMDATA);
        //$client->getRequest()->addHeader("Accept-Language", "en"); 

        if (count($parameters) > 0) {
            $client->setParameterGet($parameters);
        }
        $url = $this->getClientUrl($client);
        echo "Getting url: " . $url . "\n";


        $response = $client->send();

        switch ($response->getStatusCode()) {
            case 200:
                $list = $response->getBody();
                if (!$list) {
                    throw new \Exception("Empty list returned $uri.");
                }
                break;
            default:
                $status = $response->getStatusCode();
                $uri = $client->getRequest()->getUriString();
                $reason = $response->getReasonPhrase();
                throw new \Exception("Cannot retrieve product catalog list at $uri, http status code: $status returned ($uri), reason: $reason");
        }

        return $list;
    }

}
