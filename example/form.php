<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\Form;
use Krzysztofzylka\Console\Prints;

$environment = Form::input('Environment:', 'prod');
Prints::info('Environment: ' . $environment);

$token = Form::secretInput('Token:', null, true);
Prints::success('Secret token length: ' . strlen($token));

if (!Form::prompt('Continue?', ' [y/n]', false, true)) {
    Prints::warning('Cancelled by user.');
    exit(1);
}

$name = Form::input('Set name:', null, true, true);
Prints::success('Input value: ' . $name);
