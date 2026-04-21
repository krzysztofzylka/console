<?php

namespace Krzysztofzylka\Console\Generator;

use InvalidArgumentException;

/**
 * Table generator
 */
class Table
{

    private array $data = [];

    private array $columns = [];

    private array $style = [
        'top_left' => '┌',
        'top_fill' => '─',
        'top_sep' => '┬',
        'top_right' => '┐',
        'mid_left' => '├',
        'mid_fill' => '─',
        'mid_sep' => '┼',
        'mid_right' => '┤',
        'bottom_left' => '└',
        'bottom_fill' => '─',
        'bottom_sep' => '┴',
        'bottom_right' => '┘',
        'vertical' => '│',
    ];

    private bool $showHeader = true;

    private bool $showBorder = true;

    private ?int $maxWidth = null;

    /**
     * Add a table column definition.
     *
     * @param string $name
     * @param string $data
     * @param string $align
     * @param int|null $maxWidth
     * @param bool $truncate
     * @return void
     */
    public function addColumn(string $name, string $data, string $align = 'left', ?int $maxWidth = null, bool $truncate = false): void
    {
        $align = strtolower($align);

        if (!in_array($align, ['left', 'right', 'center'], true)) {
            throw new InvalidArgumentException("Unsupported alignment '{$align}'");
        }

        if ($maxWidth !== null && $maxWidth < 1) {
            throw new InvalidArgumentException('Column max width must be greater than zero');
        }

        $this->columns[] = [
            'name' => $name,
            'data' => $data,
            'align' => $align,
            'max_width' => $maxWidth,
            'truncate' => $truncate,
            'space' => $this->getTextWidth($name),
        ];
    }

    /**
     * Set table data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Add a single row to the table.
     *
     * @param array<string, mixed> $row
     * @return void
     */
    public function addRow(array $row): void
    {
        $this->data[] = $row;
    }

    /**
     * Toggle header rendering.
     *
     * @param bool $showHeader
     * @return void
     */
    public function setShowHeader(bool $showHeader): void
    {
        $this->showHeader = $showHeader;
    }

    /**
     * Toggle outer borders and separators.
     *
     * @param bool $showBorder
     * @return void
     */
    public function setShowBorder(bool $showBorder): void
    {
        $this->showBorder = $showBorder;
    }

    /**
     * Set a global max width for all columns.
     *
     * @param int|null $maxWidth
     * @return void
     */
    public function setMaxWidth(?int $maxWidth): void
    {
        if ($maxWidth !== null && $maxWidth < 1) {
            throw new InvalidArgumentException('Table max width must be greater than zero');
        }

        $this->maxWidth = $maxWidth;
    }

    /**
     * Render table.
     *
     * @return void
     */
    public function render(): void
    {
        print($this->renderToString() . PHP_EOL);
    }

    /**
     * Render the table to a string.
     *
     * @return string
     */
    public function renderToString(): string
    {
        if (empty($this->columns) || empty($this->data)) {
            return 'Empty table';
        }

        $this->calculateSpace();
        $lines = [];

        if ($this->showBorder) {
            $lines[] = $this->buildBorder('top');
        }

        if ($this->showHeader) {
            foreach ($this->buildRowLines(array_map(fn(array $column) => $column['name'], $this->columns), true) as $line) {
                $lines[] = $this->renderLine($line);
            }

            $lines[] = $this->showBorder
                ? $this->buildBorder('middle')
                : $this->buildPlainSeparator();
        }

        foreach ($this->data as $row) {
            $values = [];

            foreach ($this->columns as $column) {
                $values[] = $this->normalizeValue($row[$column['data']] ?? '');
            }

            foreach ($this->buildRowLines($values) as $line) {
                $lines[] = $this->renderLine($line);
            }
        }

        if ($this->showBorder) {
            $lines[] = $this->buildBorder('bottom');
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Build wrapped lines for a logical row.
     *
     * @param array<int, string> $values
     * @param bool $header
     * @return array<int, array<int, string>>
     */
    private function buildRowLines(array $values, bool $header = false): array
    {
        $cells = [];
        $rowHeight = 1;

        foreach ($this->columns as $index => $column) {
            $lines = $this->wrapText($values[$index] ?? '', $column['space'], (bool)$column['truncate']);
            $cells[$index] = $lines;
            $rowHeight = max($rowHeight, count($lines));
        }

        $rows = [];

        for ($lineIndex = 0; $lineIndex < $rowHeight; $lineIndex++) {
            $row = [];

            foreach ($this->columns as $index => $column) {
                $row[] = $this->padCell(
                    $cells[$index][$lineIndex] ?? '',
                    $column['space'],
                    $header ? 'center' : $column['align']
                );
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Render a single visual row.
     *
     * @param array<int, string> $row
     * @return string
     */
    private function renderLine(array $row): string
    {
        $row = array_map(fn(string $value) => ' ' . $value . ' ', $row);

        if ($this->showBorder) {
            return $this->style['vertical']
                . implode($this->style['vertical'], $row)
                . $this->style['vertical'];
        }

        return implode(' | ', $row);
    }

    /**
     * Build table border line.
     *
     * @param string $position
     * @return string
     */
    private function buildBorder(string $position): string
    {
        $prefix = match ($position) {
            'top' => 'top',
            'middle' => 'mid',
            'bottom' => 'bottom',
            default => throw new InvalidArgumentException("Unsupported border position '{$position}'"),
        };

        $segments = array_map(
            fn(array $column) => str_repeat($this->style[$prefix . '_fill'], $column['space'] + 2),
            $this->columns
        );

        return $this->style[$prefix . '_left']
            . implode($this->style[$prefix . '_sep'], $segments)
            . $this->style[$prefix . '_right'];
    }

    /**
     * Build plain separator used when borders are disabled.
     *
     * @return string
     */
    private function buildPlainSeparator(): string
    {
        $segments = array_map(
            fn(array $column) => str_repeat('-', $column['space'] + 2),
            $this->columns
        );

        return implode('-+-', $segments);
    }

    /**
     * Calculate column widths.
     *
     * @return void
     */
    private function calculateSpace(): void
    {
        foreach ($this->columns as $key => $column) {
            $widths = [];

            if ($this->showHeader) {
                $widths[] = $this->getLongestWrappedLineWidth($column['name'], $column['space'], $column);
            }

            foreach ($this->data as $row) {
                $value = $this->normalizeValue($row[$column['data']] ?? '');
                $widths[] = $this->getLongestWrappedLineWidth($value, $column['space'], $column);
            }

            $this->columns[$key]['space'] = max(1, ...$widths);
        }
    }

    /**
     * @param string $value
     * @param int $fallbackWidth
     * @param array{name: string, data: string, align: string, max_width: ?int, truncate: bool, space: int} $column
     * @return int
     */
    private function getLongestWrappedLineWidth(string $value, int $fallbackWidth, array $column): int
    {
        $limit = $this->resolveColumnMaxWidth($column) ?? max($fallbackWidth, $this->getTextWidth($value));
        $lines = $this->wrapText($value, $limit, (bool)$column['truncate']);
        $lineWidths = array_map([$this, 'getTextWidth'], $lines);

        return max(1, ...$lineWidths);
    }

    /**
     * @param array{name: string, data: string, align: string, max_width: ?int, truncate: bool, space: int} $column
     * @return int|null
     */
    private function resolveColumnMaxWidth(array $column): ?int
    {
        $limits = array_values(array_filter([$column['max_width'], $this->maxWidth], fn($value) => $value !== null));

        if ($limits === []) {
            return null;
        }

        return min($limits);
    }

    /**
     * Wrap text to a visible width and preserve explicit newlines.
     *
     * @param string $text
     * @param int $width
     * @param bool $truncate
     * @return array<int, string>
     */
    private function wrapText(string $text, int $width, bool $truncate = false): array
    {
        if ($width < 1) {
            return [''];
        }

        $segments = preg_split("/\R/u", $text) ?: [''];
        $lines = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                $lines[] = '';
                continue;
            }

            if ($truncate) {
                $lines[] = self::fitToWidth($segment, $width);
                continue;
            }

            foreach ($this->wrapSegment($segment, $width) as $line) {
                $lines[] = $line;
            }
        }

        return $lines === [] ? [''] : $lines;
    }

    /**
     * Word-wrap a single line segment.
     *
     * @param string $segment
     * @param int $width
     * @return array<int, string>
     */
    private function wrapSegment(string $segment, int $width): array
    {
        if ($this->getTextWidth($segment) <= $width) {
            return [$segment];
        }

        $words = preg_split('/\s+/u', trim($segment)) ?: [];

        if ($words === []) {
            return [''];
        }

        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if ($this->getTextWidth($word) > $width) {
                if ($current !== '') {
                    $lines[] = $current;
                    $current = '';
                }

                foreach ($this->chunkText($word, $width) as $chunk) {
                    $lines[] = $chunk;
                }

                continue;
            }

            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if ($this->getTextWidth($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            $lines[] = $current;
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * Split long text into width-limited chunks.
     *
     * @param string $text
     * @param int $width
     * @return array<int, string>
     */
    private function chunkText(string $text, int $width): array
    {
        $chunks = [];
        $remaining = $text;

        while ($remaining !== '') {
            $chunk = $this->trimToWidth($remaining, $width);
            $chunks[] = $chunk;
            $remaining = $this->substringByWidth($remaining, $width);
        }

        return $chunks;
    }

    /**
     * Pad text according to alignment.
     *
     * @param string $value
     * @param int $width
     * @param string $align
     * @return string
     */
    private function padCell(string $value, int $width, string $align): string
    {
        $padding = max($width - $this->getTextWidth($value), 0);

        return match ($align) {
            'right' => str_repeat(' ', $padding) . $value,
            'center' => str_repeat(' ', (int)floor($padding / 2))
                . $value
                . str_repeat(' ', (int)ceil($padding / 2)),
            default => $value . str_repeat(' ', $padding),
        };
    }

    /**
     * Normalize a cell value to string.
     *
     * @param mixed $value
     * @return string
     */
    private function normalizeValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => '',
            is_scalar($value) => (string)$value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
        };
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

    /**
     * @param string $text
     * @param int $width
     * @param string $suffix
     * @return string
     */
    private static function fitToWidth(string $text, int $width, string $suffix = '...'): string
    {
        $textWidth = function_exists('mb_strwidth') ? mb_strwidth($text, 'UTF-8') : strlen($text);

        if ($textWidth <= $width) {
            return $text;
        }

        $suffixWidth = function_exists('mb_strwidth') ? mb_strwidth($suffix, 'UTF-8') : strlen($suffix);
        $targetWidth = max(1, $width - $suffixWidth);

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $targetWidth, '', 'UTF-8') . $suffix;
        }

        return substr($text, 0, $targetWidth) . $suffix;
    }

    /**
     * Trim a string to a visible width.
     *
     * @param string $text
     * @param int $width
     * @return string
     */
    private function trimToWidth(string $text, int $width): string
    {
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $width, '', 'UTF-8');
        }

        return substr($text, 0, $width);
    }

    /**
     * Return the remaining substring after removing a width-limited chunk.
     *
     * @param string $text
     * @param int $width
     * @return string
     */
    private function substringByWidth(string $text, int $width): string
    {
        if (function_exists('mb_strimwidth')) {
            $prefix = mb_strimwidth($text, 0, $width, '', 'UTF-8');
            return mb_substr($text, mb_strlen($prefix, 'UTF-8'), null, 'UTF-8');
        }

        return substr($text, $width);
    }

}
