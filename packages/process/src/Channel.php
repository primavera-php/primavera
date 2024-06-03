<?php

namespace Primavera\Process;

use Primavera\Process\Futures\TimeoutException;
use RuntimeException;

class Channel
{
    private bool $isOpened = true;

    private Forker $forker;

    public function __construct(
        private $resource,
    ) {}

    public function setForker(Forker $forker)
    {
        $this->forker = $forker;
    }

    public function sendMessage($data, int $withSignal = null)
    {
        fwrite($this->resource, serialize($data));

        if ($this->forker && $withSignal) {
            $this->forker->sendSignal($withSignal);
        }
    }

    public function readMessage()
    {
        $data = fgets($this->resource);

        if (stream_get_meta_data($this->resource)['timed_out']) {
            throw new TimeoutException("failed to read return value, timed out!");
        }

        if ($data === false) {
            throw new RuntimeException("Failed to read from socket: " . socket_strerror(socket_last_error($this->resource)));
        }

        return unserialize($data);
    }

    public function isChannelReady(): bool
    {
        $read = [$this->resource];
        $write = [$this->resource];
        $except = [$this->resource];

        return (bool) stream_select($read, $write, $except, 0);
    }

    public function close()
    {
        if ($this->isOpened) {
            fclose($this->resource);
        }

        $this->isOpened = false;
    }

    public function setTimeout(int $timeout, int $microseconds = 0)
    {
        stream_set_timeout($this->resource, $timeout, $microseconds);
    }

    public function setBlocking(bool $bool)
    {
        stream_set_blocking($this->resource, $bool);
    }

    public function __destruct()
    {
        $this->close();
    }
}
