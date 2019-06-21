<?php

namespace SimpleConfigSystem;

use Monolog\Logger;
use \Monolog\Handler\StreamHandler;

use SimpleConfigSystem\UnSupportedFileTypeException;

class ConfigParser {

    // Define logger variables to use as MonoLog instance
    private $logger;
    private $config_file_info;
    // @var ConfigParserInterface
    private $parser;
    
    public function __construct(\SplFileInfo $config_file_info)
	{
        // Throw an exception if the provided file not exists on the system
        if (!$config_file_info->isFile()) {
            throw new \Exception(sprintf('The provided file was not found:: "%s"', $config_file_info->getPathName()));
        }

        // Initialize Monolog instance;
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler('app.log', Logger::INFO));

        // Create and set appropriate parser for provided coonfig file.
        $this->parser = $this->initParserByExtension($config_file_info->getExtension());
        $this->config_file_info = $config_file_info;

        $this->logger->info(sprintf('Parser for file with path "%s" inintialized successfully!', $this->config_file_info->getPathname()));
    }
    
    private function initParserByExtension(string $config_file_extension): ConfigParserInterface {
        $parsers = array(new JsonConfigParser(), new YmlConfigParser());
        
        foreach($parsers as $curr_parser) {
            if ($curr_parser->isSupported($config_file_extension)) {
                return $curr_parser;
            }
        }
        $this->logger->info('The provided file extension is not supported by any available parsers.');
        throw new \Exception(sprintf('Unsupported file extension for configuration files: "%s"', $config_file_extension));
    }

    public function parse(): array {
        return $this->parser->parse(file_get_contents($this->config_file_info->getPathname()));
    }

    public function serialize($config_data_array): string {
        return $this->parser->serialize($config_data_array);
    }

    



}


?>