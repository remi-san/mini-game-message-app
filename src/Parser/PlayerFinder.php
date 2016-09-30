<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;
use MiniGame\Entity\PlayerId;

interface PlayerFinder
{
    /**
     * @param  PlayerId $id
     *
     * @return ParsingPlayer
     */
    public function find($id);

    /**
     * Gets the active player for the user
     *
     * @param  ApplicationUserId $userId
     *
     * @return ParsingPlayer
     */
    public function getActivePlayerForUser(ApplicationUserId $userId);

    /**
     * @param ParsingPlayer $player
     *
     * @return void
     */
    public function save(ParsingPlayer $player);
}
