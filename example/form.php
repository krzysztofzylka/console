<?php

include('../vendor/autoload.php');

$input = \Krzysztofzylka\Console\Form::input();
\Krzysztofzylka\Console\Prints::print('Input value: ' . $input);
\Krzysztofzylka\Console\Form::prompt('Continue?');
$input = \Krzysztofzylka\Console\Form::input('Set name:');
\Krzysztofzylka\Console\Prints::print('Input value: ' . $input);