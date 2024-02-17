<?php

include('../vendor/autoload.php');

$help = new \Krzysztofzylka\Console\Generator\Help();
$help->addHeader('Header');
$help->addHelp('Help', 'Help command');
$help->addHelp('Test', 'Test message');
$help->addHeader('Parameters', 'yellow');
$help->addHelp('-parameters', 'test parameters');
$help->addHelp('-very long parameters', 'test parameters');
$help->render();