# php_configurator
A simple configuration system in PHP `7.3`

## Dependencies:

### composer
> https://getcomposer.org/
```
Used for management of the dependencies and autoloading code.
```
### monolog/monolog
> https://packagist.org/packages/monolog/monolog
```
Used as a logging library.
```
### ext-apcu
> https://www.php.net/manual/en/book.apcu.php
```
Used for access to the PHP APCu cache.
```

### symfony/yaml
> https://github.com/symfony/yaml
```
Used for parsing and serializing yml content
```

## Run

1. Place your project folder inside apache2 (or other server) Document root directory
2. Run apache2
3. Navigate from your browser to ``localhost/php_configurator/index.php`` access the demo page. 

## Usage

Import **SimpleConfigSystem** class
```
use SimpleConfigSystem\SimpleConfigSystem;
```

Create new instance of SimpleConfigSystem by providing path to a configuration file in JSON or YML format
```
$simple_config_system = new SimpleConfigSystem("data/data.json");
```

Call method for getting value by providing a path to parameter
```
$simple_config_system->getValue("servers:Neptune:ip");
```

Call method for setting value by providing a path to parameter
```
$simple_config_system->setValue("servers:Mars:type", "QA");
```

