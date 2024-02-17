<?php

include('../vendor/autoload.php');

\Krzysztofzylka\Console\Prints::print('Example value');
\Krzysztofzylka\Console\Prints::print('Example value', true);
\Krzysztofzylka\Console\Prints::print('Example value', true, false,'red');
\Krzysztofzylka\Console\Prints::print('Example value', false, false,'green');
\Krzysztofzylka\Console\Prints::print('Example value with exit', true, true);
\Krzysztofzylka\Console\Prints::print('Exit test');