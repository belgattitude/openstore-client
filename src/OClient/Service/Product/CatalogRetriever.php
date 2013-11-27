<?php

namespace OClient\Service\Product;
use OClient\Service\BaseService;

use Zend\Http\Client;

class CatalogRetriever extends BaseService {

	
	protected $api_spec  = '/api/productcatalog.{format}';
	
	
	/**
	 * Get available medias pictures 
	 * 
	 * @return array
	 */
	public function getList($format) 
	{
		$base_url = $this->getApiBaseUrl();
		$api_key = $this->getApiKey();
		$spec = $base_url  . $this->api_spec;
		
		$uri = str_replace('{format}', $format, $spec);
		
		$client = $this->getHttpClient($uri);
		//$client->setParameterGet($this->media_list_params);
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
				throw new \Exception("Cannot retrieve product catalog list at $uri, http status code: $status returned");
		}
		return $list;
	}
	
}

