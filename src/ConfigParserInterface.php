<?php

namespace SimpleConfigSystem;


interface ConfigParserInterface
{
	public function isSupported(string $file_extension): bool;

	public function parse(string $config_file_content): array;

	public function serialize(array $config_file_array): string;
}
