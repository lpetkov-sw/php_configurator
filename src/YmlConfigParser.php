<?php

namespace SimpleConfigSystem;

use Symfony\Component\Yaml\Yaml;

class YmlConfigParser implements ConfigParserInterface {

    // Store supported file extension in lowercase string format.
    CONST SUPPORTED_CONFIG_FILE_EXTENSION = "yml";

    public function isSupported(string $file_extension): bool {
        return strtolower($file_extension) === self::SUPPORTED_CONFIG_FILE_EXTENSION;
    }

	public function parse(string $config_file_content): array {
        return (array)Yaml::parse($config_file_content);
    }

    public function serialize(array $config_file_array): string {
        return Yaml::dump($config_file_array, 2, 2);
    }
}

?>