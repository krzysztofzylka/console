# krzysztofzylka/console

Small PHP console helper library for:
- colored output,
- argument parsing,
- interactive prompts,
- help rendering,
- ASCII table rendering.

## Install

```bash
composer require krzysztofzylka/console
```

## Examples

Ready-to-run examples are available in `example/`:
- `example/print.php`
- `example/form.php`
- `example/help.php`
- `example/table.php`
- `example/args.php`
- `example/progress.php`

## Output

Preferred API:

```php
use Krzysztofzylka\Console\Prints;

Prints::line('Starting job');
Prints::info('Loading configuration');
Prints::success('Done');
Prints::warning('Running in dry-run mode');
Prints::error('Something went wrong');
Prints::section('Deployment', 'Production release');
Prints::bulletList(['Validate config', 'Run sync', 'Send notification']);
Prints::numberedList(['Build', 'Deploy', 'Verify']);
Prints::kv(['env' => 'prod', 'release' => '1.5.0']);
Prints::json(['service' => 'taskello', 'healthy' => true]);
Prints::blankLine();
Prints::write('Progress: 50%');
```

Legacy aliases still work for backward compatibility:

```php
Prints::print('value');  // deprecated, use line()
Prints::sprint('value'); // deprecated, use write()
```

Formatter helpers:
- `Prints::section()` / `Prints::formatSection()`
- `Prints::bulletList()` / `Prints::formatBulletList()`
- `Prints::numberedList()` / `Prints::formatNumberedList()`
- `Prints::kv()` / `Prints::formatKv()`
- `Prints::json()` / `Prints::formatJson()`
- `Prints::blankLine()`

Utilities:
- `Prints::terminalWidth()`
- `Prints::fitLineToWidth()`

## Arguments

`Args::getArgs($argv)` returns a normalized array with script path, positional arguments and parsed options.

Supported forms:
- `--env=prod`
- `--env prod`
- `--force`
- `-project taskello`
- repeated options like `--tag v1 --tag v2`
- `--` to stop option parsing

```php
use Krzysztofzylka\Console\Args;

$parsed = Args::getArgs($argv);

/*
[
    'path' => 'bin/app.php',
    'args' => ['sync'],
    'params' => [
        'env' => 'prod',
        'force' => true,
        'tag' => ['v1', 'v2'],
    ],
]
*/
```

Schema-aware parsing:

```php
use Krzysztofzylka\Console\Args;

$parsed = Args::parse($argv, [
    'env' => ['type' => 'string', 'default' => 'dev', 'aliases' => ['e']],
    'force' => ['type' => 'bool', 'default' => false],
    'tag' => ['type' => 'string', 'multiple' => true],
    'retries' => ['type' => 'int', 'default' => 0],
]);
```

## Forms

### Input

```php
use Krzysztofzylka\Console\Form;

$name = Form::input('Name:');
$environment = Form::input('Environment:', 'prod');
$token = Form::secretInput('Token:', null, true);
```

Parameters for `Form::input()`:
- `$label` - text shown before input
- `$default` - optional default value
- `$trim` - trim input value
- `$required` - keep asking until non-empty value is provided

`Form::secretInput()` works similarly to `input()`, but hides typed characters when the current environment supports it.

### Prompt

```php
use Krzysztofzylka\Console\Form;

Form::prompt('Continue?');
Form::prompt('Continue?', ' [y/n]', false, true);
```

Parameters for `Form::prompt()`:
- `$label` - question text
- `$append` - suffix shown next to label
- `$exit` - exit the script when the answer is negative
- `$default` - default yes/no answer

Accepted positive answers:
- `y`
- `yes`
- `t`
- `tak`

Accepted negative answers:
- `n`
- `no`
- `nie`

## Help generator

```php
use Krzysztofzylka\Console\Generator\Help;

$overview = new Help();
$overview->addHeader('Commands');
$overview->addHelp('serve', 'Serve the application');
$overview->addHelp('cache:clear', 'Clear cache');
$overview->addHelp('routes:list', 'List routes');

echo $overview->renderOverview();
```

Detailed help for a single command:

```php
$help = new Help();
$help->setTitle('Sync command');
$help->setDescription('Synchronize local data with a remote source.');
$help->setUsage('php bin/app sync <workspace> [--env=prod] [--force]');
$help->addCommand('sync', 'Synchronize local data with a remote source.');
$help->addArgument('workspace', 'Workspace identifier.', true);
$help->addOption('--env', 'Environment name.', false, 'prod', false, ['dev', 'stage', 'prod']);
$help->addOption('--force', 'Run command without confirmation.');
$help->addExample('php bin/app sync main --env=prod', 'Synchronize the main workspace in production.');

echo $help->renderCommandHelp();
```

Detailed mode supports:
- title
- description
- usage
- commands
- arguments
- options
- examples
- wrapping long descriptions to terminal width

Recommended usage:
- `php bin/app` -> render compact overview
- `php bin/app serve --help` -> render detailed command help

## Table generator

```php
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

$table->render();
$output = $table->renderToString();
```

Column options:
- `$align` supports `left`, `right`, `center`
- `$maxWidth` limits visible width
- `$truncate` trims long lines with `...`

Table options:
- `addRow(array $row)`
- `setShowHeader(bool $showHeader)`
- `setShowBorder(bool $showBorder)`
- `setMaxWidth(?int $maxWidth)`

## Progress helpers

```php
use Krzysztofzylka\Console\ProgressBar;
use Krzysztofzylka\Console\Spinner;

$progress = new ProgressBar(5, 20);

for ($i = 1; $i <= 5; $i++) {
    $progress->setProgress($i, 'step ' . $i);
}

$progress->finish('completed');

$spinner = new Spinner();
$spinner->spin('Waiting...');
$spinner->finish('Done');
```

## Colors

```php
use Krzysztofzylka\Console\Helper\Color;

echo Color::wrap('Success', 'green');
echo Color::wrap('Warning', 'yellow');
echo Color::wrap('Important', 'bold');
```

Supported names include:
- `black`
- `gray`
- `red`
- `green`
- `yellow`
- `blue`
- `magenta`
- `cyan`
- `white`
- `graylight`
- `bold`
- `dim`
- `underline`
- `bg-white`
- `bg-gray`
- `bg-red`
- `bg-green`
- `bg-yellow`
- `bg-blue`
- `bg-magenta`
- `bg-cyan`

Named presets:
- `info`
- `success`
- `warning`
- `error`
- `muted`
- `title`
- `highlight`

## Notes

- ANSI colors are automatically disabled when the output stream is not a TTY or when `NO_COLOR` is set.
- `Help` and `Table` use `mb_strwidth()` when available for better UTF-8 alignment.
- `Table` supports per-column alignment, multiline cells, optional headers, optional borders and global/per-column max width.
