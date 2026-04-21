<?php

namespace Krzysztofzylka\Console;

class Spinner
{

    private array $frames = ['-', '\\', '|', '/'];

    private int $position = 0;

    private string $stream = 'stdout';

    /**
     * @param string|null $label
     * @return void
     */
    public function spin(?string $label = null): void
    {
        fwrite($this->getStream(), "\r" . $this->renderToString($label));
        $this->position = ($this->position + 1) % count($this->frames);
    }

    /**
     * @param string|null $label
     * @return void
     */
    public function finish(?string $label = 'Done'): void
    {
        $message = $label !== null ? $label : '';
        fwrite($this->getStream(), "\r" . $message . str_repeat(' ', 4) . PHP_EOL);
    }

    /**
     * @param string|null $label
     * @return string
     */
    public function renderToString(?string $label = null): string
    {
        $frame = $this->frames[$this->position];

        if ($label === null || $label === '') {
            return $frame;
        }

        return $frame . ' ' . $label;
    }

    /**
     * @param array<int, string> $frames
     * @return void
     */
    public function setFrames(array $frames): void
    {
        if ($frames !== []) {
            $this->frames = array_values($frames);
            $this->position = 0;
        }
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
     * @return resource
     */
    private function getStream()
    {
        return $this->stream === 'stderr' ? STDERR : STDOUT;
    }
}
