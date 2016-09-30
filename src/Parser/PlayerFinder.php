<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;

interface PlayerFinder
{
    /**
     * Gets the active player for the user
     *
     * @param  ApplicationUserId $userId
     *
     * @return ParsingPlayer
     */
    public function getActivePlayerForUser(ApplicationUserId $userId);
}
