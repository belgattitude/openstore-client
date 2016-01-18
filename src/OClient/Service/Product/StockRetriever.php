<?php

namespace OClient\Service\Product;

use OClient\Service\BaseService;

class StockRetriever extends BaseService
{

    protected $api_spec = '/api/productstock.{format}';

    /**
     * Get available medias pictures
     *
     * @param string $format
     * @param array $parameters
     * @return array
     */
    public function getList($format, $parameters = [])
    {
        $base_url = $this->getApiBaseUrl();
        $api_key = $this->getApiKey();
        $spec = $base_url . $this->api_spec;
        $uri = str_replace('{format}', $format, $spec);
        $parameters['api_key'] = $api_key;
        $list = $this->retrieve($uri, $parameters);
        return $list;
    }
}
