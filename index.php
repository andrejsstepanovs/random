#!/usr/bin/php
<?php

include 'autoload.php';

use \App\Service\Locator as ServiceLocator;
use \App\Resource\Arguments;


$locator = new ServiceLocator();

try {
    $arguments = (new Arguments())->setArguments($argv);
    $locator->getCommandRandom()
            ->setArguments($arguments)
            ->execute();

} catch (\Exception $exc) {
    $output = $locator->getServiceOutput();
    $output->error(get_class($exc) . ': ' . $exc->getMessage());
    $output->error('Trace: ' . $exc->getTraceAsString());
    exit(1);
}
