<?php

namespace SimpleConfigSystem;

use Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class SimpleConfigSystem  {

	private $logger;

	// Key @string, which points to the cached configuration data in APCu
	const CACHE_CONFIG_KEY = 'config_cache';
	// Key @string, which points to the last modified time of the configuration file in APCu
	const CACHE_CONFIG_LAST_MODIFIED_KEY = 'config_last_modified';
	// Configuration data property path separator
	const CONFIG_DATA_PATH_SEPARATOR = ':';

	private $config_file_parser;
	private $config_file_info;

    /**
	 * Constructor for a new SimpleConfigSystem object, which accepts a path to a configuration file
	 * 
	 * @throws \InvalidArgumentException When the provided parameter is NULL ot there is no parameter.
	 */
	public function __construct(string $config_file_path = NULL)
	{
		// Initialize Monolog instance;
        $this->logger = new Logger('name');
        $this->logger->pushHandler(new StreamHandler('app.log', Logger::INFO));

        if ($config_file_path !== NULL ) {

			// Create File info object in order to access information for the given configurateion file
            $this->config_file_info = new \SplFileInfo($config_file_path);

			// Create ConfigParser object to get the configuration file data
			$this->config_file_parser = new ConfigParser($this->config_file_info);
			
			// Update APCu cache if necessary
			$this->updateAPCuConfigData();

        } else {
			throw new \InvalidArgumentException(sprintf('Invalid configuration file path parameter: %s', $config_file_path ));
        }
	}

	/**
	 * Function for updating APCu cache if necessary on system loads
	 */
	private function updateAPCuConfigData() {
		// Check if there is cached configuration file inf APCu cache
		if (apcu_exists(self::CACHE_CONFIG_KEY)) {
			$cached_config_last_modified_time =  apcu_fetch(self::CACHE_CONFIG_LAST_MODIFIED_KEY);
			$config_file_last_modified_time = $this->config_file_info->getMTime();

			// The APCu cached config data has to be updated if the config file have new changes
			if ($cached_config_last_modified_time !== $config_file_last_modified_time) {
				// Update cached configuration file data in the APCu cache.
				apcu_store(self::CACHE_CONFIG_KEY, $this->config_file_parser->parse());
				$this->storeLastModifiedTimeInAPCu($config_file_last_modified_time);
				$this->logger->info('Updating APCu cache with new data from configuration file.');
			} else {
				$this->logger->info(sprintf('Nothing to update in APCu cache', $this->config_file_info->getPathname()));
			}

		} else {
			// Add parsed configuration file data to the APCu cache.
			apcu_add(self::CACHE_CONFIG_KEY, $this->config_file_parser->parse());
			$this->storeLastModifiedTimeInAPCu($this->config_file_info->getMTime());
		}
	}

	// Add new or overwrite existing last modified time data in the APCu cache
	private function storeLastModifiedTimeInAPCu($last_modified_time) {
		// Check if there is a cached data about the provided key
		if (apcu_exists(self::CACHE_CONFIG_LAST_MODIFIED_KEY)) {
			// Overwrite the existing data for the provided key in APCu cache
			apcu_store(self::CACHE_CONFIG_LAST_MODIFIED_KEY, $last_modified_time);
		} else {
			// Add data with the provided key to the APCu cache
			apcu_add(self::CACHE_CONFIG_LAST_MODIFIED_KEY, $last_modified_time);
		}
	}

	public function getValue(string $property_path) {
		$path_array = $this->pathToArray($property_path);
		$config_data = apcu_fetch(self::CACHE_CONFIG_KEY);

		if ( $this->isValidConfigPath($path_array) ) {
			$config_data_property = NULL;
			return $this->getConfigData($path_array, $config_data);
		}
	}

	public function setValue(string $property_path, $property_value) {
		$path_array = $this->pathToArray($property_path);

		if ( $this->isValidConfigPath($path_array) ) {

			$config_data = apcu_fetch(self::CACHE_CONFIG_KEY);

			/*
			*	APCu cache cast @arrays to @object for better perfomance
			*	In order to set data to the fetched config array we need an array type object.
			*	The function "json_decode" has functionality to convert returned objects into associative arrays.
			* 	In order to activate this functionality we set the second parameter to "true";
			*   Referrence: https://www.php.net/manual/en/function.json-decode.php
			*/
			$array_config_data = json_decode(json_encode($config_data), true);

			if ($this->setConfigData($array_config_data, $path_array, $property_value)) {
				$this->saveToDisk($array_config_data);
				// Store new changes in the APCu cache
				apcu_store(self::CACHE_CONFIG_KEY, $array_config_data);
				return true;
			}
		}
		return false;
	}

	/*
	* As a requirement the client should not be able to change the server names or add/remove servers.
	* In order to achieve this we define that the given path has to be consisted of 3 properties:
	* servers:serverName:propertyName
	*/
	private function isValidConfigPath($path_array): bool {
		if (sizeof($path_array) == 3 ) {
			return true;
		}
		throw new \InvalidArgumentException(sprintf('Invalid property path parameter "%s". Pease provide parameter in format "servers:serverName:propertyName"', $property_path ));
	}

	private function pathToArray($property_path) {
		return explode(self::CONFIG_DATA_PATH_SEPARATOR, $property_path);
	}

	private function getConfigData($path_array, $array) {
		$temp = $array;
	
		foreach($path_array as $key) {
			if ( !array_key_exists($key, $temp) ) {
				throw new \InvalidArgumentException(sprintf('There is no property "%s". for the current server. You can setValue only for existing properties.', $key ));
			}
			$temp = ((array) $temp)[$key];
		}
		return $temp;
	}

	private function setConfigData(&$array_config_data=array(), $path_array, $value=null) {
		$temp =& $array_config_data;
	
		foreach($path_array as $key) {
			if ( !array_key_exists($key, $temp) ) {
				throw new \InvalidArgumentException(sprintf('There is no property "%s". for the current server. You can setValue only for existing properties.', $key ));
			}

			if ($key === end($path_array)) {
				$temp[$key] = $value;
				return true;
			} else {
				$temp =& $temp[$key];
			}
		}
		return false;
	}

	private function saveToDisk($config_array) {
		file_put_contents($this->config_file_info->getPathname(), $this->config_file_parser->serialize($config_array));
	}

}

?>