<?php
namespace OClient\Service;

class BaseService 
{
	
	/**
	 *
	 * @var array 
	 */
	protected $apiConfiguration;
	
	
	public function __construct() 
	{
		$this->apiConfiguration = array();
	}

	
	
	
	/**
	 * 
	 * @param string $api_base_url
	 * @param string $api_key
	 * @return \OClient\Service\Product\PictureRetriever
	 */
	public function setApiConfiguration($api_base_url, $api_key) 
	{
		// Clean up eventual ending slashes.
		$api_base_url = preg_replace('/(\/)+$/', '', $api_base_url);
		$this->apiConfiguration = array();
		$this->apiConfiguration['base_url'] = $api_base_url;
		$this->apiConfiguration['api_key']  = $api_key;
		return $this;
	}	
	
	/**
	 * 
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getApiBaseUrl() 
	{
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
	protected function getApiKey() 
	{
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
	protected function getHttpClient($uri) 
	{
		$client = new Client();
		$client->setUri($uri);
		$client->setOptions(array(
			'maxredirects' => 5,
			'timeout'      => 30
		))->setHeaders(array(
			'Accept-encoding' => 'gzip,deflate',
			'X-Powered-By: OClient'			
		));
		return $client;
	}
	
}

