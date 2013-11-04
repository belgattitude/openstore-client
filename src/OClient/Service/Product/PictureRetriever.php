<?php

namespace OClient\Service\Product;
use OClient\Service\BaseService;

use Zend\Http\Client;

class PictureRetriever extends BaseService {

	
	/**
	 * @var array
	 */
	protected $media_list_params = array(
		'type' => 'PICTURE'
	);
	protected $media_list_uri  = '/api/productmedia.json';
	protected $picture_spec = '/media/picture/{media_id}_{resolution}-{quality}.jpg';

	

	
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
		$client->setParameterGet($this->media_list_params);
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
	 * @return string binary picture 
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
	
}

