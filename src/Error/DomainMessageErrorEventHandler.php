<?php

namespace MiniGameMessageApp\Error;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBus;
use Broadway\Tools\Metadata\Context\ContextEnricher;
use League\Event\EventInterface;
use MessageApp\Error\ErrorEventHandler as MessageErrorEventHandler;
use MiniGameApp\Error\ErrorEventHandler as GameErrorEventHandler;

class DomainMessageErrorEventHandler implements GameErrorEventHandler, MessageErrorEventHandler
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * Constructor
     *
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * Handles an error
     *
     * @param  EventInterface $error
     * @param  mixed          $context
     *
     * @return void
     */
    public function handle(EventInterface $error, $context = null)
    {
        $this->eventBus->publish(
            new DomainEventStream([
                DomainMessage::recordNow(
                    null,
                    null,
                    new Metadata([ ContextEnricher::CONTEXT => $context ]),
                    $error
                )
            ])
        );
    }
}
