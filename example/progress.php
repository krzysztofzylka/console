<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\ProgressBar;
use Krzysztofzylka\Console\Spinner;

$progress = new ProgressBar(5, 20);

for ($i = 1; $i <= 5; $i++) {
    usleep(100000);
    $progress->setProgress($i, 'step ' . $i);
}

$progress->finish('completed');

$spinner = new Spinner();

for ($i = 0; $i < 8; $i++) {
    $spinner->spin('Waiting...');
    usleep(100000);
}

$spinner->finish('Done');
