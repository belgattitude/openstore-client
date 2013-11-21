<?php
/**
 * DO NOT EDIT THIS FILE !!! 
 * 
 * IF YOU WANT TO MODIFY AN OPTION
 * CREATE AN ENTRY IN THE oclient.config.php INSTEAD.
 * THE CONFIGURATION oclient.config.php AND oclient.config.default.php
 * FILES WILL BE MERGED BY THE APPLICATION.
 * 
 */

$config = array(
	'api' => array(
		/**
		 * REQUIRED
		 * @var string host Base url of the api service 
		 */
		'base_url' => 'http://api.DOMAIN.extention',
		
		/**
		 * REQUIRED
		 * @var string key Api key to connect to the api service
		 */
		'key' => ''
	),
	'services' => array(
		'media' => array(
			'options' => array(
				'layout' => array(
					'supported' => array(
						'standard' => function (array $media) {
							$product_id	= $media['product_id']; 
							$suffix = ($media['flag_primary'] == 1) ? '' : "_" . $media['sort_index'];
							return $product_id . $suffix . ".jpg";
						},
						'bybrand' => function(array $media) {
							$product_id	= $media['product_id']; 
							$suffix = ($media['flag_primary'] == 1) ? '' : "_" . $media['sort_index'];
							$subdir = preg_replace('/[^A-Z0-9-_]/', '_', strtoupper($media['brand_reference']));
							if ($subdir == '') $subdir = 'UNKNOWN_BRAND';
							return $subdir . DIRECTORY_SEPARATOR . $product_id . $suffix . ".jpg";
						},
						'bybrandref' => function(array $media) {
							$product_reference	= $media['product_reference']; 
							$suffix = ($media['flag_primary'] == 1) ? '' : " (" . $media['sort_index'] . ")";
							$subdir = preg_replace('/[^A-Z0-9-_]/', '_', strtoupper($media['brand_reference']));
							if ($subdir == '') $subdir = 'UNKNOWN_BRAND';
							$filename = preg_replace('/[^A-Z0-9-\_\.\ ]/', '_', strtoupper($product_reference));
							return $subdir . DIRECTORY_SEPARATOR . $filename . $suffix . ".jpg";
						}		
								
					),
					'default' => 'standard'
				),
				'type' => array(
					'supported' => array('picture' => 'Product pictures'),
					'default'	=> 'picture'
				),
				'picture_resolution' => array(
					'supported' => array(
							'40x40',
							'65x90',		
							'170x200',		
							'250x750',		
							'800x800', 
							'1024x768',		
							'1280x1024',	
							'1200x1200',
							'3000x3000'
					),
					'default'   => '1200x1200'
				),
				'picture_quality' => array(
					'supported' => array(95, 90, 85, 80),
					'default' => 90
				)
			)
		)
	)
);
return $config;