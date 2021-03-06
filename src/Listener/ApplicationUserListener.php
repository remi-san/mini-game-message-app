<?php

namespace MiniGameMessageApp\Listener;

use League\Event\EventInterface;
use League\Event\ListenerInterface;
use MiniGame\Event\PlayerCreatedEvent;
use MiniGameMessageApp\Store\MiniGameUserStore;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ApplicationUserListener implements ListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MiniGameUserStore
     */
    private $store;

    /**
     * Constructor
     *
     * @param MiniGameUserStore $store
     */
    public function __construct(
        MiniGameUserStore $store
    ) {
        $this->store = $store;
        $this->logger = new NullLogger();
    }

    /**
     * Handle an event.
     *
     * @param EventInterface $event
     *
     * @return void
     */
    public function handle(EventInterface $event)
    {
        if (! $event instanceof PlayerCreatedEvent) {
            return;
        }

        $applicationUser = $this->store->find($event->getExternalReference());

        $applicationUser->linkToPlayer($event->getGameId(), $event->getPlayerId());

        $this->store->save($applicationUser);
    }

    /**
     * Check whether the listener is the given parameter.
     *
     * @param mixed $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $this == $listener;
    }
}
