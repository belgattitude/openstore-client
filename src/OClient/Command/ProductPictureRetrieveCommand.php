<?php

namespace OClient\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use OClient\Service;
use Zend\Config\Config;

class ProductPictureRetrieveCommand extends Command
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
    public function __construct(Config $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('product:picture:retrieve')
                ->setDescription('Retrieve product pictures and medias.')
                ->addArgument(
                    'path',
                    InputArgument::REQUIRED,
                    'What is the path where to save the pictures ?'
                )
                ->addOption(
                    'layout',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Layout to use for generating directory structure'
                )
                ->addOption(
                    'resolution',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Resolution of the pictures i.e: 800x800'
                )
                ->addOption(
                    'quality',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Compression quality of the pictures i.e: 90'
                )
                ->addOption(
                    'brands',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'What are the brands to retrieve (multiple separated by ,)'
                )
                ->addOption(
                    'overwrite',
                    null,
                    InputOption::VALUE_NONE,
                    'Overwrite existing picture files.'
                )


        ;
    }

    /**
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $options = $this->getOptions($input, $output);

        $api_url = $this->config->api->base_url;
        $api_key = $this->config->api->key;
        $layout_manager = $options['layout'];

        $service = new Service\Product\PictureRetriever();
        $service->setApiConfiguration($api_url, $api_key);

        //if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
        $output->writeln("Retrieving picture list");
        //}


        $medias = $service->getMedias($options);
        $count_media = count($medias['data']);



        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $count_media);
        $progress->setRedrawFrequency(5);

        $overwrite = $options['overwrite'];


        $resolution = $options['resolution'];
        $quality = $options['quality'];

        foreach ($medias['data'] as $media) {
            $output_path = $options['path'] . DIRECTORY_SEPARATOR . $layout_manager($media);

            if (!file_exists($output_path) || filemtime($output_path) < (int) $media['filemtime'] || $options['overwrite'] || filesize($output_path) == 0) {
                if (!is_dir(dirname($output_path))) {
                    $ret = mkdir(dirname($output_path), $mode = 0777, $recursive = true);
                    if (!$ret) {
                        throw new \Exception("Cannot create path : " . dirname($output_path));
                    }
                }


                if (false) {
                    // Old deprecated method to retrieve picture file
                    $picture = $service->getMedia($media['media_id'], $resolution, $quality);
                    $ret = file_put_contents($output_path, $picture);
                } else {
                    $tmp_url = $media['picture_url'];
                    $picture_specs = preg_replace('/(\d+x\d+\-\d+)/', '{resolution}-{quality}', $tmp_url);

                    $url = str_replace(['{resolution}', '{quality}'], [$resolution, $quality], $picture_specs);

                    try {
                        $picture = $service->retrieveMediaUrl($url, $media['product_id']);
                        $ret = file_put_contents($output_path, $picture);
                        if (!$ret) {
                            throw new \Exception("Cannot save image : " . $output_path);
                        }
                    } catch (\Exception $e) {
                        echo "[Error] " . $e->getMessage() . "\n";
                    } 
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
    protected function getOptions(InputInterface $input, OutputInterface $output)
    {
        $c = $this->config->services->media;
        if ($c === null) {
            throw new \RuntimeException('Missing configuration section config.services.media, please check your installation and configuration.');
        }

        $options = [];

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

        $supported_layouts = array_keys($c->options->layout->supported->toArray());
        $default_layout = $c->options->layout->default;


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
            $layouts = implode(',', $supported_layouts);
            throw new \RunTimeException(
                "Selected layout not supported '$layout', accepted values are '$layouts'"
            );
        }

        $options['layout'] = $c->options->layout->supported->get($layout);


        //
        // Step 2: Resolution selection
        //

        $supported_resolutions = $c->options->picture_resolution->supported->toArray();
        $default_resolution = $c->options->picture_resolution->default;

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
            $resolutions = implode(',', $supported_resolutions);
            throw new \RunTimeException(
                "Selected resolution not supported '$resolution', accepted values are '$resolutions'"
            );
        }

        $options['resolution'] = $resolution;

        //
        // Step 3 : Quality selection
        //

        $supported_qualities = $c->options->picture_quality->supported->toArray();
        $default_quality = $c->options->picture_quality->default;

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
            $qualities = implode(',', $supported_qualities);
            throw new \RunTimeException(
                "Selected quality not supported '$quality', accepted values are '$qualities'"
            );
        }

        $options['quality'] = $quality;

        // Overwrite

        $options['overwrite'] = $input->getOption('overwrite');

        //
        // Step 3 : checking brands
        //
        if ($input->hasOption('brands')) {
            if (trim($input->getOption('brands')) != '') {
                $brs = explode(',', trim($input->getOption('brands')));
                $brands = [];
                foreach ($brs as $brand) {
                    if (!preg_match('/^([A-Z0-9-\_\ ])+$/', strtoupper(trim($brand)))) {
                        throw new \RuntimeException(
                            "Brand reference '$brand' is not valid, brands read '{$input->getOption('brands')}'"
                        );
                    } else {
                        $brands[] = strtoupper(trim($brand));
                    }
                }
                $options['brands'] = implode(',', $brands);
            } else {
                $options['brands'] = null;
            }
        }



        return $options;
    }
}
