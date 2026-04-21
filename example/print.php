<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\Prints;

Prints::section('Deployment', 'Example formatter helpers');
Prints::line('Example value');
Prints::line('Example value with timestamp', true);
Prints::line('Example red value', true, 'red');
Prints::success('Example success value');
Prints::warning('Example warning value');
Prints::error('Example error value');
Prints::bulletList([
    'Validate configuration',
    'Run synchronization',
    'Send notification',
]);
Prints::numberedList([
    'First step',
    'Second step',
    'Third step',
]);
Prints::kv([
    'env' => 'prod',
    'release' => '1.5.0',
    'status' => 'success',
]);
Prints::json([
    'service' => 'taskello',
    'healthy' => true,
    'workers' => 4,
]);
Prints::blankLine();
Prints::write('No newline here...');
Prints::line(' done');
