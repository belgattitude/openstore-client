<?php

namespace OClient\Service\Product;
use OClient\Service\BaseService;

use Zend\Http\Client;

class CatalogRetriever extends BaseService {

	
	protected $api_spec  = '/api/productcatalog.{format}';
	
	
	/**
	 * Get available medias pictures 
	 * 
	 * @param string $format
	 * @param array $parameters
	 * @return array
	 */
	public function getList($format, $parameters=array()) 
	{
		$base_url = $this->getApiBaseUrl();
		$api_key = $this->getApiKey();
		$spec = $base_url  . $this->api_spec;
		$uri = str_replace('{format}', $format, $spec);
		
		$list = $this->retrieve($uri, $parameters);
		return $list;
	}
	
}

