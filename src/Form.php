<?php

namespace Krzysztofzylka\Console;

class Form
{

    /**
     * Console input
     * @param string|null $label
     * @param string|null $labelColor
     * @return string
     */
    public static function input(?string $label = null, ?string $labelColor = 'blue'): string
    {
        if (!is_null($label)) {
            Prints::print($label, color: $labelColor);
        }

        Prints::sprint('> ');
        $input = (string)fgets(STDIN);

        return trim($input);
    }

    /**
     * Prompt
     * @param string $label
     * @param string|null $labelColor
     * @param string $append
     * @param bool $exit
     * @return bool
     */
    public static function prompt(string $label, ?string $labelColor = 'blue', string $append = ' [y/n]', bool $exit = true): bool
    {
        Prints::print($label . $append, color: $labelColor);
        $confirm = strtolower(trim(fgets(STDIN)));
        $confirmContinue = in_array($confirm, ['y', 't']);

        if ($exit && !$confirmContinue) {
            exit;
        }

        return $confirmContinue;
    }

}