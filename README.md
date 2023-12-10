# Install
```bash
composer require krzysztofzylka/console
```

# Methods
## Print
```php
\Krzysztofzylka\Console\Prints::print('value');
```
## Simple print
```php
\Krzysztofzylka\Console\Prints::sprint('value');
```
## Get args
```php
\Krzysztofzylka\Console\Args::getArgs($argv)
```
## Generate helper
```php
$help = new \krzysztofzylka\Console\Generator\Help();
$help->addHeader('Help');
$help->addHelp('help', 'Show help');
$help->addHelp('add', 'Add description');
echo $help->render();
```