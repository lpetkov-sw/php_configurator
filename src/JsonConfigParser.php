<?php

namespace SimpleConfigSystem;

class JsonConfigParser implements ConfigParserInterface {

    // Store supported file extension in lowercase string format.
    CONST SUPPORTED_CONFIG_FILE_EXTENSION = "json";

    public function isSupported(string $file_extension): bool {
        return strtolower($file_extension) === self::SUPPORTED_CONFIG_FILE_EXTENSION;
    }

	public function parse(string $config_file_content): array {
        return json_decode($config_file_content, true);
    }

    public function serialize(array $config_file_array): string {
        return (string)json_encode($config_file_array, JSON_PRETTY_PRINT);
    }
    
}

?>