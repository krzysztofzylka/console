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
    public static function input(?string $label = null): string
    {
        $input = readline($label !== null ? ($label . ' ') : '');

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
    public static function prompt(string $label, string $append = ' [y/n]', bool $exit = true): bool
    {
        $confirm = self::input($label . $append);
        $confirmContinue = in_array(strtolower($confirm), ['y', 't']);

        if ($exit && !$confirmContinue) {
            exit;
        }

        return $confirmContinue;
    }

}