<?php

namespace OClient\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use OClient\Service;

use Zend\Config\Config;

class ProductCatalogRetrieveCommand extends Command
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
		
		
		$this->setName('product:catalog:retrieve')
				->setDescription('Retrieve product pricelist.')
				->addArgument(
						'filename', InputArgument::REQUIRED, 'What is the output filename ?'
				)
				->addOption(
						'pricelist', null, InputOption::VALUE_REQUIRED, 'What is the pricelist you want to retrieve ?'
				)
				->addOption(
						'language', null, InputOption::VALUE_REQUIRED, 'What is the language you want to retrieve (en/fr/de...) ?'
				)
				
				->addOption(
						'brands', null, InputOption::VALUE_OPTIONAL, 'What are the brands to retrieve (multiple separated by ,)'
				)
				->addOption(
						'format', null, InputOption::VALUE_REQUIRED, 'Format of the output file xml/json...'
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
		
		$service = new Service\Product\CatalogRetriever();
		$service->setApiConfiguration($api_url, $api_key);
		
		//if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
			$output->writeln("Retrieving product catalog list");
		//}
		
		$format = $options['format'];

		$parameters = array(
			'language' => $options['language'],
			'pricelist' => $options['pricelist'],
			'brands' => $options['brands']
  		);
		
		$list = $service->getList($format, $parameters);
		
		file_put_contents($options['filename'], $list);
		
		//if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
		$output->writeln("Success");
		
		//} 

		
	}

	/**
	 * 
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return array
	 */
	protected function getOptions(InputInterface $input, OutputInterface $output) {

		$c = $this->config->services->list;
		if ($c === null) {
			throw new \RuntimeException('Missing configuration section config.services.list, please check your installation and configuration.');
		}
		
		
		$options = array();

		$dialog = $this->getHelperSet()->get('dialog');
		
		//
		// Step 0: Checking path existence
		//
		
		$filename = trim($input->getArgument('filename'));
		$path = dirname($filename);
		
		if (!is_dir($path)) {
			throw new \RunTimeException(
                "Selected path does not exists '$path'"
            );			
		}
		
		if (!is_writable($path)) {
			throw new \RunTimeException(
                "Selected path is not writable '$path'"
            );			
		}
		$options['filename'] = $filename;

		// 
		// Step 1: Language selection
		//
		
		$language = trim($input->getOption('language'));

		if ($language == '') {
			throw new \RunTimeException(
                "Language option is not valid"
            );			
		}
		
		if (strlen($language) != 2) {
			throw new \RunTimeException(
                "Language code must be to alpha characters, read '$language'"
            );			
		}
		
		if (!preg_match('/^([A-Z])+$/', strtoupper($language))) {
			throw new \RunTimeException(
                "Language code is not valid, read '$language'"
            );			
		}
		
		
		$options['language'] = $language;

		
		//
		// Step 2 : checking pricelist 
		//
		$pricelist = trim($input->getOption('pricelist'));

		if ($pricelist == '') {
			throw new \RunTimeException(
                "Pricelist option is not valid"
            );			
		}
		
		if (!preg_match('/^([A-Za-z0-9-\_])+$/', strtoupper($pricelist))) {
			throw new \RunTimeException(
                "Pricelist code is not valid, read '$pricelist'"
            );			
		}
		
		
		$options['pricelist'] = $pricelist;
		
		
		//
		// Step 3 : checking brands 
		//
		if ($input->hasOption('brands')) {
			if (trim($input->getOption('brands')) != '') {
				$brs = explode(',', trim($input->getOption('brands')));
				$brands = array();
				foreach($brs as $brand) {
					if (!preg_match('/^([A-Z0-9-\_\ ])+$/', strtoupper(trim($brand)))) {
						throw new \RuntimeException(
								"Brand reference '$brand' is not valid, brands read '{$input->getOption('brands')}'"
								);
					} else {
						$brands[] = trim($brand);
					}
				}
				$options['brands'] = join(',', $brands);
			} else {
				$options['brands'] = null;
			}
		}
		
		
		// 
		// Step 4: Format selection
		//
		
		$format = trim($input->getOption('format'));

		$supported_formats		= $c->options->format->supported->toArray();
		$default_format			= $c->options->format->default;
		
		
		if ($format == '') {
			$idx = $dialog->select(
					$output, 
					"Please select the format (default to $default_format)", 
					$supported_formats, 
					$default_format
			);
			$format = $supported_formats[$idx];
			$output->writeln('You have just selected: ' . $supported_formats[$idx]);
		}
		if (!in_array($format, $supported_formats)) {
			$formats = join(',', $supported_formats);
			throw new \RunTimeException(
                "Selected format not supported '$format', accepted values are '$formats'"
            );			
		}
		
		$options['format'] = $format;
		
	
		
		return $options;
	}
	
}
