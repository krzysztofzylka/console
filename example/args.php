<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\Args;
use Krzysztofzylka\Console\Prints;

$parsed = Args::parse($argv, [
    'env' => ['type' => 'string', 'default' => 'dev', 'aliases' => ['e']],
    'force' => ['type' => 'bool', 'default' => false],
    'tag' => ['type' => 'string', 'multiple' => true],
    'retries' => ['type' => 'int', 'default' => 0],
]);

Prints::section('Parsed arguments');
Prints::kv([
    'path' => $parsed['path'],
    'args' => implode(', ', $parsed['args']),
    'params' => Prints::formatJson($parsed['params']),
]);
