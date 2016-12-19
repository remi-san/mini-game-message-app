<?php
namespace MiniGameMessageApp\Test\Mock;

use League\Event\EventInterface;
use MiniGame\Event\PlayerCreatedEvent;

interface MiniGamePlayerCreatedEvent extends PlayerCreatedEvent, EventInterface
{
}
