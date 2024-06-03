<?php

namespace Primavera\Process;

use BadMethodCallException;
use OutOfBoundsException;
use RuntimeException;
use Primavera\Process\Channel;
use Throwable;

class Forker
{
    private int $pid;

    private $status;

    private ?Channel $channel = null;

    private bool $isChild = false;

    private bool $exiting = false;

    private bool $forked = false;

    public function __construct()
    {
        pcntl_async_signals(true);

        $listenChildKill = function ($signal) {
            if (!$this->isChild) {
                throw new OutOfBoundsException("this signal {$signal} should not be listened by a parent process");
            }

            switch ($signal) {
                case SIGTERM:
                case SIGHUP:
                case SIGINT:
                    $this->exiting = true;
                    break;
            }
        };

        pcntl_signal(SIGTERM, $listenChildKill);
        pcntl_signal(SIGHUP, $listenChildKill);
        pcntl_signal(SIGINT, $listenChildKill);
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getStatus()
    {
        if ($this->isChild()) {
            throw new BadMethodCallException("this method is meant to be called by parent process only");
        }

        return $this->status;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function isChild(): bool
    {
        return $this->isChild;
    }

    public function isExiting()
    {
        if (!$this->isChild()) {
            throw new BadMethodCallException("this method is meant to be called by child processes only!!");
        }

        return $this->exiting;
    }

    /**
     * @return Channel[]
     */
    public function createChannelPair(): array
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        if ($sockets === false) {
            throw new RuntimeException("Failed to create socket pair.");
        }

        return [
            new Channel($sockets[0]),
            new Channel($sockets[1]),
        ];
    }

    public function listenChildExit(callable $callable)
    {
        pcntl_signal(SIGCHLD, function () use ($callable) {
            $this->wait();

            $callable($this->pid, $this->status);
        });
    }

    public function fork(callable $callable, array $args = [], bool $useChannels = true, bool $daemonize = false)
    {
        if ($this->forked) {
            throw new RuntimeException("this forker has already been forked");
        }

        $this->forked = true;

        [$childChannel, $parentChannel] = $useChannels ? $this->createChannelPair() : [null, null];

        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new RuntimeException("Failed to fork: " . pcntl_strerror(pcntl_get_last_error()));
        } elseif ($pid) {
            // Parent process
            $this->pid = $pid;
            $this->forked = true;

            if ($useChannels) {
                $childChannel->close();
                $this->channel = $parentChannel;
            }

            if ($daemonize) {
                $this->wait();
            }
        } else {
            // Child process
            $this->isChild = true;
            $this->pid = posix_getppid();

            if ($useChannels) {
                $parentChannel->close();
                $this->channel = $childChannel;
            }

            array_unshift($args, $this);

            try {
                $callable(...$args);
            } catch (Throwable $e) {
                if (!$useChannels) {
                    exit($e);
                }

                $childChannel->sendMessage($e);
            }

            if ($useChannels) {
                try {
                    $childChannel->close();
                } catch (Throwable $e) {
                    exit($e);
                }
            }

            exit;
        }
    }

    public function kill()
    {
        posix_kill($this->pid, SIGTERM);
        $this->wait();
    }

    public function wait()
    {
        pcntl_waitpid($this->pid, $this->status, WUNTRACED);
    }

    public function sendSignal(int $signal)
    {
        posix_kill($this->pid, $signal);
    }

    public function __destruct()
    {
        if (!$this->isChild) {
            $this->wait();
        }
    }
}