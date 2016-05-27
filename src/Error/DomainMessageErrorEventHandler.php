<?php

namespace MiniGameMessageApp\Error;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\Tools\Metadata\Context\ContextEnricher;
use League\Event\EventInterface;
use MessageApp\Error\ErrorEventHandler as MessageErrorEventHandler;
use MiniGameApp\Error\ErrorEventHandler as GameErrorEventHandler;

class DomainMessageErrorEventHandler implements GameErrorEventHandler, MessageErrorEventHandler
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

    /**
     * Constructor
     *
     * @param EventBusInterface $eventBus
     */
    public function __construct(EventBusInterface $eventBus)
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
        $message = DomainMessage::recordNow(
            null,
            null,
            new Metadata(
                [
                    ContextEnricher::CONTEXT => $context
                ]
            ),
            $error
        );
        $this->eventBus->publish(new DomainEventStream([$message]));
    }
}
