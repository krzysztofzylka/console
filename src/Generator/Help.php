<?php

namespace Krzysztofzylka\Console\Generator;

use Krzysztofzylka\Console\Helper\Color;
use Krzysztofzylka\Console\Prints;

/**
 * Help generator
 */
class Help
{

    private array $helpers = [];

    private ?string $title = null;

    private ?string $description = null;

    private ?string $usage = null;

    private array $commands = [];

    private array $arguments = [];

    private array $options = [];

    private array $examples = [];

    /**
     * Set help title.
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Set help description.
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Set usage line.
     *
     * @param string $usage
     * @return void
     */
    public function setUsage(string $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * Add help entry for list mode.
     *
     * @param string $command
     * @param string $message
     * @return void
     */
    public function addHelp(string $command, string $message): void
    {
        $this->helpers[] = [$command, $message, 'type' => 'help'];
    }

    /**
     * Add command entry for detailed mode.
     *
     * @param string $name
     * @param string $description
     * @return void
     */
    public function addCommand(string $name, string $description): void
    {
        $this->commands[] = [
            'name' => $name,
            'description' => $description,
        ];
    }

    /**
     * Add argument description for detailed mode.
     *
     * @param string $name
     * @param string $description
     * @param bool $required
     * @param string|null $default
     * @param bool $multiple
     * @param array<int, string> $acceptedValues
     * @return void
     */
    public function addArgument(
        string $name,
        string $description,
        bool $required = false,
        ?string $default = null,
        bool $multiple = false,
        array $acceptedValues = []
    ): void {
        $this->arguments[] = [
            'name' => $name,
            'description' => $description,
            'required' => $required,
            'default' => $default,
            'multiple' => $multiple,
            'accepted_values' => $acceptedValues,
        ];
    }

    /**
     * Add option description for detailed mode.
     *
     * @param string $name
     * @param string $description
     * @param bool $required
     * @param string|null $default
     * @param bool $multiple
     * @param array<int, string> $acceptedValues
     * @return void
     */
    public function addOption(
        string $name,
        string $description,
        bool $required = false,
        ?string $default = null,
        bool $multiple = false,
        array $acceptedValues = []
    ): void {
        $this->options[] = [
            'name' => $name,
            'description' => $description,
            'required' => $required,
            'default' => $default,
            'multiple' => $multiple,
            'accepted_values' => $acceptedValues,
        ];
    }

    /**
     * Add example entry for detailed mode.
     *
     * @param string $command
     * @param string|null $description
     * @return void
     */
    public function addExample(string $command, ?string $description = null): void
    {
        $this->examples[] = [
            'command' => $command,
            'description' => $description,
        ];
    }

    /**
     * Add console header.
     *
     * @param string $title
     * @param int|string|null $color
     * @return void
     */
    public function addHeader(string $title, null|int|string $color = null): void
    {
        $this->helpers[] = ['', $title, 'type' => 'header', 'color' => $color];
    }

    /**
     * Render help.
     *
     * @return void
     */
    public function render(): void
    {
        Prints::line($this->renderToString());
    }

    /**
     * Render the help definition to a string.
     *
     * @return string
     */
    public function renderToString(): string
    {
        if ($this->hasDetailedData()) {
            return $this->renderAsDetail();
        }

        return $this->renderAsList();
    }

    /**
     * Render compact command overview.
     *
     * @return string
     */
    public function renderOverview(): string
    {
        return $this->renderAsList();
    }

    /**
     * Render detailed help for a single command.
     *
     * @return string
     */
    public function renderCommandHelp(): string
    {
        return $this->renderAsDetail();
    }

    /**
     * Render simple list mode.
     *
     * @return string
     */
    public function renderAsList(): string
    {
        $lines = [];
        $commandWidth = $this->getCommandWidth();
        $supportsColors = Color::supportsColors();
        $availableWidth = max(20, Prints::terminalWidth() - $commandWidth - 3);

        foreach ($this->helpers as $data) {
            switch ($data['type']) {
                case 'header':
                    $lines[] = Color::wrap($data[1], $data['color'] ?? 'blue', $supportsColors);
                    break;
                default:
                    $left = $data[0]
                        . str_repeat(' ', max($commandWidth - $this->getTextWidth($data[0]), 0))
                        . ' - ';
                    $wrapped = $this->wrapText($data[1], $availableWidth);

                    foreach ($wrapped as $index => $line) {
                        $lines[] = $index === 0
                            ? $left . $line
                            : str_repeat(' ', $this->getTextWidth($left)) . $line;
                    }
                    break;
            }
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Render detailed help mode.
     *
     * @return string
     */
    public function renderAsDetail(): string
    {
        $lines = [];
        $supportsColors = Color::supportsColors();

        if ($this->title !== null) {
            $lines[] = Color::wrap($this->title, 'blue', $supportsColors);
            $lines[] = Color::wrap(str_repeat('=', max(3, $this->getTextWidth($this->title))), 'blue', $supportsColors);
        }

        if ($this->description !== null) {
            if ($lines !== []) {
                $lines[] = '';
            }

            foreach ($this->wrapText($this->description, Prints::terminalWidth()) as $line) {
                $lines[] = $line;
            }
        }

        if ($this->usage !== null) {
            $this->appendSection($lines, 'Usage', [$this->usage], $supportsColors, false);
        }

        if ($this->commands !== []) {
            $this->appendNamedEntriesSection($lines, 'Commands', $this->commands, $supportsColors);
        }

        if ($this->arguments !== []) {
            $this->appendNamedEntriesSection($lines, 'Arguments', $this->arguments, $supportsColors, true);
        }

        if ($this->options !== []) {
            $this->appendNamedEntriesSection($lines, 'Options', $this->options, $supportsColors, true);
        }

        if ($this->examples !== []) {
            $exampleLines = [];

            foreach ($this->examples as $example) {
                $exampleLines[] = $example['command'];

                if (!empty($example['description'])) {
                    foreach ($this->wrapText((string)$example['description'], max(20, Prints::terminalWidth() - 4)) as $line) {
                        $exampleLines[] = '  ' . $line;
                    }
                }
            }

            $this->appendSection($lines, 'Examples', $exampleLines, $supportsColors, false);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return bool
     */
    private function hasDetailedData(): bool
    {
        return $this->title !== null
            || $this->description !== null
            || $this->usage !== null
            || $this->arguments !== []
            || $this->options !== []
            || $this->examples !== [];
    }

    /**
     * @param array<int, string> $lines
     * @param string $title
     * @param array<int, string> $content
     * @param bool $supportsColors
     * @param bool $indent
     * @return void
     */
    private function appendSection(array &$lines, string $title, array $content, bool $supportsColors, bool $indent = true): void
    {
        if ($content === []) {
            return;
        }

        if ($lines !== []) {
            $lines[] = '';
        }

        $lines[] = Color::wrap($title, 'yellow', $supportsColors);

        foreach ($content as $line) {
            $lines[] = $indent ? '  ' . $line : $line;
        }
    }

    /**
     * @param array<int, string> $lines
     * @param string $title
     * @param array<int, array<string, mixed>> $entries
     * @param bool $supportsColors
     * @param bool $withMeta
     * @return void
     */
    private function appendNamedEntriesSection(
        array &$lines,
        string $title,
        array $entries,
        bool $supportsColors,
        bool $withMeta = false
    ): void {
        if ($entries === []) {
            return;
        }

        if ($lines !== []) {
            $lines[] = '';
        }

        $lines[] = Color::wrap($title, 'yellow', $supportsColors);
        $nameWidth = max(array_map(fn(array $entry) => $this->getTextWidth((string)$entry['name']), $entries));
        $availableWidth = max(20, Prints::terminalWidth() - $nameWidth - 7);

        foreach ($entries as $entry) {
            $meta = $withMeta ? $this->formatMeta($entry) : null;
            $description = (string)$entry['description'];

            if ($meta !== null) {
                $description .= ' ' . $meta;
            }

            $wrapped = $this->wrapText($description, $availableWidth);
            $prefix = '  ' . Color::wrap(
                str_pad((string)$entry['name'], $nameWidth, ' ', STR_PAD_RIGHT),
                'cyan',
                $supportsColors
            ) . '  ';

            foreach ($wrapped as $index => $line) {
                $lines[] = $index === 0
                    ? $prefix . $line
                    : str_repeat(' ', $nameWidth + 4) . $line;
            }
        }
    }

    /**
     * @param array<string, mixed> $entry
     * @return string|null
     */
    private function formatMeta(array $entry): ?string
    {
        $parts = [];

        if (($entry['required'] ?? false) === true) {
            $parts[] = 'required';
        }

        if (($entry['multiple'] ?? false) === true) {
            $parts[] = 'multiple';
        }

        if (($entry['default'] ?? null) !== null) {
            $parts[] = 'default: ' . $entry['default'];
        }

        if (($entry['accepted_values'] ?? []) !== []) {
            $parts[] = 'values: ' . implode(', ', $entry['accepted_values']);
        }

        if ($parts === []) {
            return null;
        }

        return '[' . implode('; ', $parts) . ']';
    }

    /**
     * @return int
     */
    private function getCommandWidth(): int
    {
        $commands = array_column(array_filter($this->helpers, fn(array $data) => $data['type'] === 'help'), 0);

        if ($commands === []) {
            return 0;
        }

        return max(array_map([$this, 'getTextWidth'], $commands));
    }

    /**
     * @param string $text
     * @param int $width
     * @return array<int, string>
     */
    private function wrapText(string $text, int $width): array
    {
        $segments = preg_split("/\R/u", $text) ?: [''];
        $lines = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                $lines[] = '';
                continue;
            }

            if (function_exists('mb_strwidth') && function_exists('mb_substr')) {
                $lines = array_merge($lines, $this->wrapUtf8Line($segment, $width));
                continue;
            }

            $wrapped = wordwrap($segment, $width, PHP_EOL, true);
            $lines = array_merge($lines, explode(PHP_EOL, $wrapped));
        }

        return $lines === [] ? [''] : $lines;
    }

    /**
     * @param string $text
     * @param int $width
     * @return array<int, string>
     */
    private function wrapUtf8Line(string $text, int $width): array
    {
        if ($this->getTextWidth($text) <= $width) {
            return [$text];
        }

        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if ($this->getTextWidth($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            if ($this->getTextWidth($word) <= $width) {
                $current = $word;
                continue;
            }

            foreach ($this->chunkUtf8($word, $width) as $chunk) {
                $lines[] = $chunk;
            }

            $current = '';
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * @param string $text
     * @param int $width
     * @return array<int, string>
     */
    private function chunkUtf8(string $text, int $width): array
    {
        $chunks = [];
        $current = '';
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $candidate = $current . $char;

            if ($this->getTextWidth($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $chunks[] = $current;
            }

            $current = $char;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Calculate the visible width of a string.
     *
     * @param string $text
     * @return int
     */
    private function getTextWidth(string $text): int
    {
        return function_exists('mb_strwidth') ? mb_strwidth($text, 'UTF-8') : strlen($text);
    }

}
