<?php

namespace MiniGameMessageApp\Finder;

use MiniGame\Entity\PlayerId;
use MiniGameApp\ReadModel\Player;

interface ApplicationPlayerFinder
{
    /**
     * @param  PlayerId $id
     *
     * @return Player
     */
    public function find($id);

    /**
     * @param  Player $player
     *
     * @return void
     */
    public function save(Player $player);
}
