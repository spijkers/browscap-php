<?php
declare(strict_types = 1);

namespace BrowscapPHP\Command;

use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Helper\LoggerHelper;
use Doctrine\Common\Cache\FilesystemCache;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to convert a downloaded Browscap ini file and write it to the cache
 */
class ConvertCommand extends Command
{
    /**
     * @var string
     */
    private $defaultIniFile;

    /**
     * @var string
     */
    private $defaultCacheFolder;

    public function __construct(string $defaultCacheFolder, string $defaultIniFile)
    {
        $this->defaultCacheFolder = $defaultCacheFolder;
        $this->defaultIniFile = $defaultIniFile;

        parent::__construct();
    }

    protected function configure() : void
    {
        $this
            ->setName('browscap:convert')
            ->setDescription('Converts an existing browscap.ini file to a cache.php file.')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'Path to the browscap.ini file',
                $this->defaultIniFile
            )
            ->addOption(
                'cache',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Where the cache files are located',
                $this->defaultCacheFolder
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $logger = LoggerHelper::createDefaultLogger($output);

        $fileCache = new FilesystemCache($input->getOption('cache'));
        $cache = new SimpleCacheAdapter($fileCache);

        $logger->info('initializing converting process');

        $browscap = new BrowscapUpdater($cache, $logger);

        $logger->info('started converting local file');

        $file = $input->getArgument('file');
        if (! $file) {
            $file = $this->defaultIniFile;
        }

        $browscap->convertFile($file);

        $logger->info('finished converting local file');
    }
}
