<?php

namespace OClient\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use OClient\Service;

use Zend\Config\Config;

class ProductInfoRetrieveCommand extends Command
{
	/**
	 *
	 * @var Config 
	 */
	protected $config;

	/**
	 * 
	 * @param \Zend\Config\Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
		parent::__construct();
	}
	
	
	protected function configure() {
		
		
		$this->setName('product:info:retrieve')
				->setDescription('Retrieve product catalog information.')
				->addArgument(
						'path', InputArgument::REQUIRED, 'What is the path where to save the files ?'
				)
				->addOption(
						'format', null, InputOption::VALUE_REQUIRED, 'Format to export file (xml, json, csv, xls)'
				)
				->addOption(
						'charset', null, InputOption::VALUE_REQUIRED, 'Charset to use for encoding file i.e: UTF-8'
				)
				->addOption(
						'quality', null, InputOption::VALUE_REQUIRED, 'Compression quality of the pictures i.e: 90'
				)
				
				
		;
	}


	/**
	 * 
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		$options = $this->getOptions($input, $output);
		
		
		$api_url		= $this->config->api->base_url;
		$api_key		= $this->config->api->key;
		$layout_manager = $options['layout'];
		
		$service = new Service\Product\PictureRetriever();
		$service->setApiConfiguration($api_url, $api_key);
		
		//if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
			$output->writeln("Retrieving picture list");
		//}
		
		
		$medias = $service->getMedias();
		$count_media = count($medias['data']);

		$progress = $this->getHelperSet()->get('progress');
		$progress->start($output, $count_media);		
		$progress->setRedrawFrequency(5);		
		
		$overwrite = $options['overwrite'];

		
		foreach ($medias['data'] as $media) {
			
			$output_path = $options['path'] . DIRECTORY_SEPARATOR . $layout_manager($media);
			if (!file_exists($output_path) || filemtime($output_path) < (int) $media['filemtime'] 
					|| $options['overwrite'] || filesize($output_path) == 0) {			
				
				if (!is_dir(dirname($output_path))) {
					$ret = mkdir(dirname($output_path), $mode=0777, $recursive=true);
					if (!$ret) {
						throw new \Exception("Cannot create path : " . dirname($output_path));
					}
				}
				$picture = $service->getMedia($media['media_id'], $options['resolution'], $options['quality']);				
				$ret = file_put_contents($output_path, $picture);
				if (!$ret) {
					throw new \Exception("Cannot save image : " . $output_path);
				}
			}
			$progress->advance();
		}
		$progress->finish();
		
		
		
		//$mediaRetriever = new \OClient\Api\Media\ProductPicture($api_url, $api_key, $media_url);
		

		//if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
		$output->writeln("Success");
		$output->writeln("Total pictures synced : $count_media");
		//} 

		
	}

	/**
	 * 
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return array
	 */
	protected function getOptions(InputInterface $input, OutputInterface $output) {

		$c = $this->config->services->media;
		if ($c === null) {
			throw new \RuntimeException('Missing configuration section config.services.media, please check your installation and configuration.');
		}
		
		$options = array();

		$dialog = $this->getHelperSet()->get('dialog');
		
		//
		// Step 0: Getting path
		//
		
		$path = trim($input->getArgument('path'));
		
		if (!is_dir($path) || !file_exists($path)) {
			throw new \RunTimeException(
                "Selected path does not exists '$path'"
            );			
		}
		
		if (!is_writable($path)) {
			throw new \RunTimeException(
                "Selected path is not writable '$path'"
            );			
		}
		$options['path'] = $path;

		
		// 
		// Step 1: Layout selection
		//
		
		$layout = trim($input->getOption('layout'));

		$supported_layouts		= array_keys($c->options->layout->supported->toArray());
		$default_layout			= $c->options->layout->default;
		
		
		if ($layout == '') {
			$idx = $dialog->select(
					$output, 
					"Please select the directory layout (default to $default_layout)", 
					$supported_layouts, 
					$default_layout
			);
			$layout = $supported_layouts[$idx];
			$output->writeln('You have just selected: ' . $supported_layouts[$idx]);
		}
		if (!in_array($layout, $supported_layouts)) {
			$layouts = join(',', $supported_layouts);
			throw new \RunTimeException(
                "Selected layout not supported '$layout', accepted values are '$layouts'"
            );			
		}
		
		$options['layout'] = $c->options->layout->supported->get($layout);
		
	
		//
		// Step 2: Resolution selection 
		//

		$supported_resolutions	= $c->options->picture_resolution->supported->toArray(); 
		$default_resolution		= $c->options->picture_resolution->default;
		
		$resolution = trim($input->getOption('resolution'));
		if ($resolution == '') {
			$resolution = $dialog->select(
					$output, 
					"Please select the resolution of the pictures (default to $default_resolution)", 
					$supported_resolutions, 
					$default_resolution
			);
			$output->writeln('You have just selected: ' . $supported_resolutions[$resolution]);
			$resolution = $supported_resolutions[$resolution];
		}
		if (!in_array($resolution, $supported_resolutions)) {
			$resolutions = join(',', $supported_resolutions);
			throw new \RunTimeException(
                "Selected resolution not supported '$resolution', accepted values are '$resolutions'"
            );			
		}
		
		$options['resolution'] = $resolution;
		
		//
		// Step 3 : Quality selection
		//
		
		$supported_qualities	= $c->options->picture_quality->supported->toArray();
		$default_quality		= $c->options->picture_quality->default;

		$quality = trim($input->getOption('quality'));
		if ($quality == '') {
			$quality = $dialog->select(
					$output, 
					"Please select the quality of the pictures (default to $default_quality)", 
					$supported_qualities, 
					$default_quality
			);
			$output->writeln('You have just selected: ' . $supported_qualities[$quality]);
			$quality = $supported_qualities[$quality];
		}
		
		if (!in_array($quality, $supported_qualities)) {
			$qualities = join(',', $supported_qualities);
			throw new \RunTimeException(
                "Selected quality not supported '$quality', accepted values are '$qualities'"
            );			
		}
		
		$options['quality'] = $quality;
		
		// Overwrite
		
		$options['overwrite'] = $input->getOption('overwrite');
		
		
		return $options;
	}
	
}
