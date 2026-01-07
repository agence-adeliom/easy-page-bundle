<?php

namespace Adeliom\EasyPageBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EasyPageLayoutListenerEarlyStopEvent extends Event
{
    public function __construct(
        private Request $request,
        private bool $earlyStop = false,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function isEarlyStop(): bool
    {
        return $this->earlyStop;
    }

    public function setEarlyStop(bool $earlyStop): void
    {
        $this->earlyStop = $earlyStop;
    }
}
