<?php
namespace MiniGameMessageApp\Test\Mock;

use League\Event\EventInterface;
use MiniGame\Result\AllPlayersResult;

interface AllResultEvent extends AllPlayersResult, EventInterface
{
}
