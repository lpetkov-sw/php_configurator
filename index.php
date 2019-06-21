<?php
    
    /*
    * * Autoload all PHP code located in the /src folder.
    *
    * The /vendor/autoload.php file is generated from the Composer dependency manager, based on the configurations in composer.json file. 
    */
    require __DIR__ . '/vendor/autoload.php';

    use SimpleConfigSystem\ConfigReaderJson;
    use SimpleConfigSystem\Backdrop;
    use SimpleConfigSystem\SimpleConfigSystem;

    // Creating new instance of SimpleConfigSystem by providing path to a configuration file in JSON or YML format
    $simple_config_system = new SimpleConfigSystem("data/data.json");


    // USAGE:

    echo "<br/>";
    echo 'getValue("servers:Neptune:ip") -> ' . $simple_config_system->getValue("servers:Neptune:ip");
    echo "<br/>";

    echo "<br/>";
    echo 'setValue("servers:Mars:type", "QA") -> ' . $simple_config_system->setValue("servers:Mars:type", "QA");
    echo "<br/>";
    
?>
