<?php

namespace Primavera\Process\Futures;

use LogicException;
use Primavera\Process\Channel;
use Primavera\Process\Forker;
use Throwable;

class Future implements FutureInterface
{
    private bool $isRunning = false;

    private bool $isDone = false;

    private bool $isCanceled = false;

    private $status;

    private int $pid;

    private $result = null;

    private Channel $channel;

    private Forker $forker;

    public function __construct(Forker $forker, callable $callable, ...$args)
    {
        $this->forker = $forker;

        $forker->listenChildExit(function ($pid, $status) {
            $this->isRunning = false;
            $this->isDone = true;
        });

        $forker->fork(
            function (Channel $childChannel) use ($callable, $args) {
                $childChannel->sendMessage($callable(...$args));
            },
        );

        $this->pid = $forker->getPid();
        $this->isRunning = true;
        $this->channel = $forker->getChannel();

    }

    private function checkChild()
    {
        if ($this->forker->isChild()) {
            throw new LogicException("This method cannot be called on a child process");
        }
    }

    public function cancel()
    {
        $this->checkChild();

        if (!$this->isCanceled && $this->isRunning) {
            $this->forker->kill();
        }

        $this->forker->wait();

        $this->isCanceled = true;
        $this->isRunning = false;
        $this->isDone = true;
    }

    public function canceled(): bool
    {
        $this->checkChild();

        return $this->isCanceled;
    }

    public function running(): bool
    {
        $this->checkChild();

        return $this->isRunning;
    }

    public function done(): bool
    {
        $this->checkChild();

        return $this->isDone;
    }

    public function result(int $timeout = null)
    {
        $this->checkChild();

        $this->channel->setTimeout($timeout ?? PHP_INT_MAX);

        if ($this->result === null) {
            $data = null;

            try {
                $data = $this->channel->readMessage();
            } catch (TimeoutException $e) {
                $this->cancel();
                throw $e;
            } catch (Throwable $e) {
                $this->forker->wait();
                throw $e;
            }

            $this->result = $data;
            
            if ($this->result instanceof Throwable) {
                throw $this->result;
            }
        }
        
        return $this->result;
    }
}
