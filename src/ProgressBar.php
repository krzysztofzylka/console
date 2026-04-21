<?php

namespace Krzysztofzylka\Console;

use InvalidArgumentException;

class ProgressBar
{

    private int $total;

    private int $current = 0;

    private int $width;

    private string $fillCharacter = '=';

    private string $emptyCharacter = ' ';

    private string $stream = 'stdout';

    public function __construct(int $total, int $width = 30)
    {
        if ($total < 1) {
            throw new InvalidArgumentException('Progress total must be greater than zero');
        }

        if ($width < 5) {
            throw new InvalidArgumentException('Progress width must be at least 5');
        }

        $this->total = $total;
        $this->width = $width;
    }

    /**
     * @param int $step
     * @param string|null $label
     * @return void
     */
    public function advance(int $step = 1, ?string $label = null): void
    {
        $this->setProgress($this->current + $step, $label);
    }

    /**
     * @param int $current
     * @param string|null $label
     * @return void
     */
    public function setProgress(int $current, ?string $label = null): void
    {
        $this->current = max(0, min($this->total, $current));
        $this->render($label);
    }

    /**
     * @param string|null $label
     * @return void
     */
    public function finish(?string $label = null): void
    {
        $this->current = $this->total;
        $this->render($label);
        Prints::blankLine(1, $this->stream);
    }

    /**
     * @param string|null $label
     * @return void
     */
    public function render(?string $label = null): void
    {
        fwrite($this->getStream(), "\r" . $this->renderToString($label));
    }

    /**
     * @param string|null $label
     * @return string
     */
    public function renderToString(?string $label = null): string
    {
        $ratio = $this->current / $this->total;
        $filled = (int)round($ratio * $this->width);
        $bar = str_repeat($this->fillCharacter, $filled) . str_repeat($this->emptyCharacter, $this->width - $filled);
        $suffix = sprintf(' %3d%% (%d/%d)', (int)round($ratio * 100), $this->current, $this->total);

        if ($label !== null && $label !== '') {
            $suffix .= ' ' . $label;
        }

        return '[' . $bar . ']' . $suffix;
    }

    /**
     * @param string $stream
     * @return void
     */
    public function setStream(string $stream): void
    {
        $this->stream = $stream;
    }

    /**
     * @param string $fillCharacter
     * @param string $emptyCharacter
     * @return void
     */
    public function setCharacters(string $fillCharacter = '=', string $emptyCharacter = ' '): void
    {
        $this->fillCharacter = $fillCharacter;
        $this->emptyCharacter = $emptyCharacter;
    }

    /**
     * @return resource
     */
    private function getStream()
    {
        return $this->stream === 'stderr' ? STDERR : STDOUT;
    }
}
