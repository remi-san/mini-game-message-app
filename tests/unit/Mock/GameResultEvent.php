<?php
namespace MiniGameMessageApp\Test\Mock;

use League\Event\EventInterface;
use MiniGame\GameResult;

interface GameResultEvent extends GameResult, EventInterface
{
}
