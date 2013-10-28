<?php

namespace OClient\Service\Product;


use Zend\Http\Client;

class PictureRetriever {

	
	/**
	 *
	 * @var array 
	 */
	protected $apiConfiguration;
	
	
	protected $media_list_uri  = '/api/media.json';
	protected $picture_spec = '/media/picture/{media_id}_{resolution}-{quality}.jpg';

	
	public function __construct() 
	{
		$this->apiConfiguration = array();
	}

	
	/**
	 * Get available medias pictures 
	 * 
	 * @return array
	 */
	public function getMedias() 
	{
		$base_url = $this->getApiBaseUrl();
		$api_key = $this->getApiKey();
		$uri = $base_url  . $this->media_list_uri;
		
		$client = $this->getHttpClient($uri);
		$response = $client->send();
		switch ($response->getStatusCode()) {
			case 200:
				$list = json_decode($response->getBody(), $assoc=true);
				if (!$list) {
					throw new \Exception("Cannot decode returned JSON from $uri.");
				}
				break;
			default:
				$status = $response->getStatusCode();
				throw new \Exception("Cannot retrieve picture list at $uri, http status code: $status returned");
		}
		
		return $list;
	}
	
	
	/**
	 * 
	 * @param int $media_id
	 * @param string $resolution
	 * @param int $quality
	 * @return type
	 * @throws \Exception
	 */
	public function getMedia($media_id, $resolution, $quality) 
	{
		
		$base_url = $this->getApiBaseUrl();
		$api_key = $this->getApiKey();
		$spec = $base_url  . $this->picture_spec;
		$uri = str_replace(array('{media_id}', '{resolution}', '{quality}'), 
					array($media_id, $resolution, $quality), $spec);
		
		
		$client = $this->getHttpClient($uri);
		$response = $client->send();
		switch ($response->getStatusCode()) {
			case 200:
				$binary = $response->getBody();
				if (!$binary) {
					throw new \Exception("Empty picture string.");
				}
				break;
			default:
				$status = $response->getStatusCode();
				throw new \Exception("Cannot retrieve picture at $uri, http status code: $status returned");
		}
		return $binary;
		
		
		
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

