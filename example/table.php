<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\Generator\Table;

$table = new Table();
$table->addColumn('Name', 'name');
$table->addColumn('Role', 'role', 'center');
$table->addColumn('Environment', 'environment');
$table->addColumn('Notes', 'notes', 'left', 18, true);
$table->addColumn('Requests', 'requests', 'right');
$table->addRow([
    'name' => 'Anna',
    'role' => 'Developer',
    'environment' => 'prod',
    'notes' => "Owns API rollout\nSupports on-call",
    'requests' => 1280,
]);
$table->addRow([
    'name' => 'Łukasz',
    'role' => 'QA',
    'environment' => 'stage',
    'notes' => 'Regression tests and smoke checks',
    'requests' => 315,
]);
$table->addRow([
    'name' => 'Marta',
    'role' => 'DevOps',
    'environment' => 'dev',
    'notes' => 'Infrastructure updates and observability',
    'requests' => 42,
]);

$table->render();

print(PHP_EOL);

$compact = new Table();
$compact->addColumn('Key', 'key');
$compact->addColumn('Value', 'value', 'right');
$compact->setShowBorder(false);
$compact->setShowHeader(false);
$compact->addRow(['key' => 'env', 'value' => 'prod']);
$compact->addRow(['key' => 'build', 'value' => '123']);

$compact->render();
