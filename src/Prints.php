<?php

namespace Krzysztofzylka\Console;

use Krzysztofzylka\Console\Helper\Color;

class Prints
{

    /**
     * Print data
     * @param string $value
     * @param bool $timestamp
     * @param bool $exit
     * @param string|int|null $color line color
     * @return void
     * @deprecated Use line() instead.
     */
    public static function print(string $value, bool $timestamp = false, bool $exit = false, null|string|int $color = null): void
    {
        self::line($value, $timestamp, $color, 'stdout', $exit);
    }

    /**
     * Simple print
     *
     * @deprecated Use write() instead.
     *
     * @param string $value
     * @return void
     */
    public static function sprint(string $value): void
    {
        self::write($value);
    }

    /**
     * Write text without a trailing newline.
     *
     * @param string $value
     * @param bool $timestamp
     * @param string|int|null $color
     * @param string $stream
     * @return void
     */
    public static function write(string $value, bool $timestamp = false, null|string|int $color = null, string $stream = 'stdout'): void
    {
        self::output($value, $timestamp, $color, $stream, false);
    }

    /**
     * Write text with a trailing newline.
     *
     * @param string $value
     * @param bool $timestamp
     * @param string|int|null $color
     * @param string $stream
     * @param bool $exit
     * @return void
     */
    public static function line(
        string $value,
        bool $timestamp = false,
        null|string|int $color = null,
        string $stream = 'stdout',
        bool $exit = false
    ): void {
        self::output($value, $timestamp, $color, $stream, true);

        if ($exit) {
            exit;
        }
    }

    /**
     * Print informational message to stdout.
     *
     * @param string $value
     * @param bool $timestamp
     * @return void
     */
    public static function info(string $value, bool $timestamp = false): void
    {
        self::line($value, $timestamp, 'blue', 'stdout');
    }

    /**
     * Print success message to stdout.
     *
     * @param string $value
     * @param bool $timestamp
     * @return void
     */
    public static function success(string $value, bool $timestamp = false): void
    {
        self::line($value, $timestamp, 'green', 'stdout');
    }

    /**
     * Print warning message to stdout.
     *
     * @param string $value
     * @param bool $timestamp
     * @return void
     */
    public static function warning(string $value, bool $timestamp = false): void
    {
        self::line($value, $timestamp, 'yellow', 'stdout');
    }

    /**
     * Print error message to stderr.
     *
     * @param string $value
     * @param bool $timestamp
     * @param bool $exit
     * @return void
     */
    public static function error(string $value, bool $timestamp = false, bool $exit = false): void
    {
        self::line($value, $timestamp, 'red', 'stderr', $exit);
    }

    /**
     * Render and print a CLI section header.
     *
     * @param string $title
     * @param string|null $description
     * @param string|int|null $color
     * @param string $stream
     * @return void
     */
    public static function section(
        string $title,
        ?string $description = null,
        null|string|int $color = 'blue',
        string $stream = 'stdout'
    ): void {
        self::line(self::formatSection($title, $description, $color, self::supportsColors($stream)), false, null, $stream);
    }

    /**
     * Render and print a bullet list.
     *
     * @param array<int, string> $items
     * @param string $bullet
     * @param string $stream
     * @return void
     */
    public static function bulletList(array $items, string $bullet = '- ', string $stream = 'stdout'): void
    {
        self::line(self::formatBulletList($items, $bullet), false, null, $stream);
    }

    /**
     * Render and print a numbered list.
     *
     * @param array<int, string> $items
     * @param string $stream
     * @return void
     */
    public static function numberedList(array $items, string $stream = 'stdout'): void
    {
        self::line(self::formatNumberedList($items), false, null, $stream);
    }

    /**
     * Render and print aligned key-value pairs.
     *
     * @param array<string, mixed> $pairs
     * @param string $separator
     * @param string|int|null $keyColor
     * @param string $stream
     * @return void
     */
    public static function kv(
        array $pairs,
        string $separator = ' : ',
        null|string|int $keyColor = 'cyan',
        string $stream = 'stdout'
    ): void {
        self::line(
            self::formatKv($pairs, $separator, $keyColor, self::supportsColors($stream)),
            false,
            null,
            $stream
        );
    }

    /**
     * Print formatted JSON.
     *
     * @param mixed $data
     * @param bool $pretty
     * @param string $stream
     * @return void
     */
    public static function json(mixed $data, bool $pretty = true, string $stream = 'stdout'): void
    {
        self::line(self::formatJson($data, $pretty), false, null, $stream);
    }

    /**
     * Print one or more blank lines.
     *
     * @param int $count
     * @param string $stream
     * @return void
     */
    public static function blankLine(int $count = 1, string $stream = 'stdout'): void
    {
        fwrite(self::getStream($stream), str_repeat(PHP_EOL, max(1, $count)));
    }

    /**
     * Format a section block to string.
     *
     * @param string $title
     * @param string|null $description
     * @param string|int|null $color
     * @param bool $enableColors
     * @return string
     */
    public static function formatSection(
        string $title,
        ?string $description = null,
        null|string|int $color = 'blue',
        bool $enableColors = true
    ): string {
        $title = self::fitLineToWidth($title);
        $description = $description !== null ? self::fitLineToWidth($description) : null;
        $underline = str_repeat('=', max(3, strlen($title)));
        $titleLine = Color::wrap($title, $color, $enableColors);
        $underlineLine = Color::wrap($underline, $color, $enableColors);

        if ($description === null || $description === '') {
            return $titleLine . PHP_EOL . $underlineLine;
        }

        return $titleLine . PHP_EOL . $underlineLine . PHP_EOL . $description;
    }

    /**
     * Format a bullet list to string.
     *
     * @param array<int, string> $items
     * @param string $bullet
     * @return string
     */
    public static function formatBulletList(array $items, string $bullet = '- '): string
    {
        return implode(PHP_EOL, array_map(
            fn(string $item) => self::fitLineToWidth($bullet . $item),
            $items
        ));
    }

    /**
     * Format a numbered list to string.
     *
     * @param array<int, string> $items
     * @return string
     */
    public static function formatNumberedList(array $items): string
    {
        $lines = [];

        foreach (array_values($items) as $index => $item) {
            $lines[] = self::fitLineToWidth(($index + 1) . '. ' . $item);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Format aligned key-value pairs to string.
     *
     * @param array<string, mixed> $pairs
     * @param string $separator
     * @param string|int|null $keyColor
     * @param bool $enableColors
     * @return string
     */
    public static function formatKv(
        array $pairs,
        string $separator = ' : ',
        null|string|int $keyColor = 'cyan',
        bool $enableColors = true
    ): string {
        if ($pairs === []) {
            return '';
        }

        $keyWidth = max(array_map('strlen', array_keys($pairs)));
        $terminalWidth = self::terminalWidth();
        $lines = [];

        foreach ($pairs as $key => $value) {
            $normalizedValue = self::normalizeValue($value);
            $valueLines = preg_split("/\R/u", $normalizedValue) ?: [''];
            $formattedKey = str_pad($key, $keyWidth, ' ', STR_PAD_RIGHT);
            $formattedKey = Color::wrap($formattedKey, $keyColor, $enableColors);

            foreach ($valueLines as $index => $line) {
                $availableWidth = max(10, $terminalWidth - $keyWidth - strlen($separator));
                $line = self::fitLineToWidth($line, $availableWidth);

                if ($index === 0) {
                    $lines[] = $formattedKey . $separator . $line;
                    continue;
                }

                $lines[] = str_repeat(' ', $keyWidth) . str_repeat(' ', strlen($separator)) . $line;
            }
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Format data as JSON.
     *
     * @param mixed $data
     * @param bool $pretty
     * @return string
     */
    public static function formatJson(mixed $data, bool $pretty = true): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($data, $flags);

        return $json === false ? 'null' : $json;
    }

    /**
     * Output text to the selected stream.
     *
     * @param string $value
     * @param bool $timestamp
     * @param string|int|null $color
     * @param string $stream
     * @param bool $newLine
     * @return void
     */
    private static function output(
        string $value,
        bool $timestamp,
        null|string|int $color,
        string $stream,
        bool $newLine
    ): void {
        $prefix = $timestamp ? '[' . date('Y-m-d H:i:s') . '] ' : '';
        $output = $prefix . $value . ($newLine ? PHP_EOL : '');

        if ($color !== null) {
            $output = Color::wrap($output, $color, self::supportsColors($stream));
        }

        fwrite(self::getStream($stream), $output);
    }

    /**
     * @return resource
     */
    private static function getStream(string $stream)
    {
        return $stream === 'stderr' ? STDERR : STDOUT;
    }

    /**
     * Determine whether ANSI colors should be used for a stream.
     *
     * @param string $stream
     * @return bool
     */
    private static function supportsColors(string $stream): bool
    {
        $resource = self::getStream($stream);

        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty($resource);
        }

        if (function_exists('posix_isatty')) {
            return posix_isatty($resource);
        }

        return true;
    }

    /**
     * Normalize formatter values to string.
     *
     * @param mixed $value
     * @return string
     */
    private static function normalizeValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => '',
            is_scalar($value) => (string)$value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
        };
    }

    /**
     * Get terminal width with a safe fallback.
     *
     * @param int $fallback
     * @return int
     */
    public static function terminalWidth(int $fallback = 80): int
    {
        $columns = getenv('COLUMNS');

        if (is_string($columns) && ctype_digit($columns)) {
            return max(20, (int)$columns);
        }

        $tput = shell_exec('tput cols 2>/dev/null');

        if (is_string($tput)) {
            $tput = trim($tput);

            if ($tput !== '' && ctype_digit($tput)) {
                return max(20, (int)$tput);
            }
        }

        return max(20, $fallback);
    }

    /**
     * Truncate a line to terminal width.
     *
     * @param string $value
     * @param int|null $width
     * @param string $suffix
     * @return string
     */
    public static function fitLineToWidth(string $value, ?int $width = null, string $suffix = '...'): string
    {
        $width ??= self::terminalWidth();

        if ($width < 1) {
            return '';
        }

        if (self::textWidth($value) <= $width) {
            return $value;
        }

        $suffixWidth = self::textWidth($suffix);
        $targetWidth = max(1, $width - $suffixWidth);

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($value, 0, $targetWidth, '', 'UTF-8') . $suffix;
        }

        return substr($value, 0, $targetWidth) . $suffix;
    }

    /**
     * @param string $value
     * @return int
     */
    private static function textWidth(string $value): int
    {
        return function_exists('mb_strwidth') ? mb_strwidth($value, 'UTF-8') : strlen($value);
    }

}
