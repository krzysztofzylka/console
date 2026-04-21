<?php

include(__DIR__ . '/../../../autoload.php');

use Krzysztofzylka\Console\Generator\Help;

$overview = new Help();
$overview->addHeader('Commands');
$overview->addHelp('serve', 'Serve the application');
$overview->addHelp('completion', 'Generate bash completion script');
$overview->addHelp('cache:clear', 'Clear cache');
$overview->addHelp('routes:list', 'List routes');

print($overview->renderOverview() . PHP_EOL . PHP_EOL);

$detail = new Help();
$detail->setTitle('Sync command');
$detail->setDescription('Synchronize local data with a remote source and show a readable CLI help layout.');
$detail->setUsage('php bin/app sync <workspace> [--env=prod] [--force]');
$detail->addCommand('sync', 'Synchronize local data with a remote source and show a readable CLI help layout.');
$detail->addArgument('workspace', 'Workspace identifier that should be synchronized.', true);
$detail->addOption('--env', 'Environment name.', false, 'prod', false, ['dev', 'stage', 'prod']);
$detail->addOption('--force', 'Run without confirmation.');
$detail->addExample('php bin/app sync main --env=prod', 'Synchronize the main workspace in production.');
$detail->addExample('php bin/app sync demo --env=stage --force', 'Run stage sync without asking for confirmation.');

print($detail->renderCommandHelp() . PHP_EOL);
