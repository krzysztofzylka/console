<?php

namespace Krzysztofzylka\Console;

class Form
{

    /**
     * Read text input from the console.
     *
     * @param string|null $label
     * @param string|null $default
     * @param bool $trim
     * @param bool $required
     * @return string
     */
    public static function input(?string $label = null, ?string $default = null, bool $trim = true, bool $required = false): string
    {
        do {
            $prompt = self::buildPrompt($label, $default);
            $input = function_exists('readline') ? readline($prompt) : fgets(STDIN);
            $input = $input === false ? '' : ($trim ? trim($input) : rtrim($input, "\r\n"));

            if ($input === '' && $default !== null) {
                return $default;
            }

            if (!$required || $input !== '') {
                return $input;
            }
        } while (true);
    }

    /**
     * Read a secret value without echoing characters when supported.
     *
     * @param string|null $label
     * @param string|null $default
     * @param bool $required
     * @return string
     */
    public static function secretInput(?string $label = null, ?string $default = null, bool $required = false): string
    {
        do {
            $prompt = self::buildPrompt($label, $default);
            $input = self::readHiddenInput($prompt);
            $input = $input === false ? '' : trim($input);

            if ($input === '' && $default !== null) {
                return $default;
            }

            if (!$required || $input !== '') {
                return $input;
            }
        } while (true);
    }

    /**
     * Ask for a yes/no confirmation.
     *
     * @param string $label
     * @param string $append
     * @param bool $exit
     * @param bool|null $default
     * @return bool
     */
    public static function prompt(string $label, string $append = ' [y/n]', bool $exit = true, ?bool $default = null): bool
    {
        do {
            $confirm = strtolower(self::input($label . $append, $default !== null ? ($default ? 'y' : 'n') : null));
            $confirmContinue = match ($confirm) {
                'y', 'yes', 't', 'tak' => true,
                'n', 'no', 'nie' => false,
                default => null,
            };

            if ($confirmContinue !== null) {
                if ($exit && !$confirmContinue) {
                    exit;
                }

                return $confirmContinue;
            }
        } while (true);
    }

    /**
     * Build the prompt label displayed to the user.
     *
     * @param string|null $label
     * @param string|null $default
     * @return string
     */
    private static function buildPrompt(?string $label, ?string $default = null): string
    {
        if ($label === null) {
            return '';
        }

        $suffix = $default !== null ? ' [' . $default . ']' : '';

        return $label . $suffix . ' ';
    }

    /**
     * @param string $prompt
     * @return string|false
     */
    private static function readHiddenInput(string $prompt): string|false
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return function_exists('readline') ? readline($prompt) : fgets(STDIN);
        }

        $sttyMode = shell_exec('stty -g');

        if (!is_string($sttyMode) || trim($sttyMode) === '') {
            return function_exists('readline') ? readline($prompt) : fgets(STDIN);
        }

        fwrite(STDOUT, $prompt);
        shell_exec('stty -echo');

        try {
            $value = fgets(STDIN);
        } finally {
            shell_exec('stty ' . trim($sttyMode));
            fwrite(STDOUT, PHP_EOL);
        }

        return $value;
    }

}
