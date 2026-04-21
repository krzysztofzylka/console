<?php

namespace Krzysztofzylka\Console\Helper;

class Color
{

    private const array COLORS = [
        'black' => 30,
        'gray' => 90,
        'red' => 91,
        'green' => 92,
        'yellow' => 93,
        'blue' => 94,
        'magenta' => 95,
        'cyan' => 96,
        'white' => 97,
        'graylight' => 98,
        'bold' => 1,
        'dim' => 2,
        'underline' => 4,
        'bg-white' => 7,
        'bg-gray' => 100,
        'bg-red' => 101,
        'bg-green' => 102,
        'bg-yellow' => 103,
        'bg-blue' => 104,
        'bg-magenta' => 105,
        'bg-cyan' => 106,
    ];

    private const array PRESETS = [
        'info' => ['blue'],
        'success' => ['green'],
        'warning' => ['yellow'],
        'error' => ['red'],
        'muted' => ['gray'],
        'title' => ['bold', 'blue'],
        'highlight' => ['bold', 'magenta'],
    ];

    /**
     * Generate an ANSI start sequence for a color or style.
     *
     * @param int|string|null $color
     * @return string
     */
    public static function generateColor(null|int|string $color = null): string
    {
        if (!self::supportsColors()) {
            return '';
        }

        $color = self::getColorNumber($color);

        return "\033[{$color}m";
    }

    /**
     * Wrap text with ANSI color codes.
     *
     * @param string $text
     * @param int|string|null $color
     * @param bool $enabled
     * @return string
     */
    public static function wrap(string $text, null|int|string $color = null, bool $enabled = true): string
    {
        if (!$enabled || $color === null) {
            return $text;
        }

        return "\033[" . self::getColorNumber($color) . 'm' . $text . "\033[0m";
    }

    /**
     * Generate an ANSI sequence from multiple styles.
     *
     * @param array<int, int|string> $styles
     * @param bool $enabled
     * @return string
     */
    public static function generateSequence(array $styles, bool $enabled = true): string
    {
        if (!$enabled || $styles === []) {
            return '';
        }

        $codes = array_map(fn(int|string $style) => (string)self::getColorNumber($style), $styles);

        return "\033[" . implode(';', $codes) . 'm';
    }

    /**
     * Wrap text using a named preset.
     *
     * @param string $text
     * @param string $preset
     * @param bool $enabled
     * @return string
     */
    public static function wrapPreset(string $text, string $preset, bool $enabled = true): string
    {
        if (!$enabled) {
            return $text;
        }

        $styles = self::getPresetStyles($preset);

        if ($styles === []) {
            return $text;
        }

        return self::generateSequence($styles, true) . $text . "\033[0m";
    }

    /**
     * Resolve a color or style name to its ANSI numeric code.
     *
     * @param int|string|null $color
     * @return int|string
     */
    public static function getColorNumber(null|int|string $color = null): int|string
    {
        if (is_int($color)) {
            return $color;
        }

        if (is_null($color)) {
            return 0;
        }

        return self::COLORS[$color] ?? 0;
    }

    /**
     * Get style list for a named preset.
     *
     * @param string $preset
     * @return array<int, string>
     */
    public static function getPresetStyles(string $preset): array
    {
        return self::PRESETS[$preset] ?? [];
    }

    /**
     * Detect whether ANSI colors are supported on the current stdout stream.
     *
     * @return bool
     */
    public static function supportsColors(): bool
    {
        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        if (!defined('STDOUT')) {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty(STDOUT);
        }

        if (function_exists('posix_isatty')) {
            return posix_isatty(STDOUT);
        }

        return true;
    }

}
