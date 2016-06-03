<?php

namespace MiniGameMessageApp\ReadModel\Finder;

use MessageApp\User\ApplicationUserId;
use MiniGame\Entity\MiniGameId;
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
     * Gets the active player for the user
     *
     * @param  ApplicationUserId $userId
     *
     * @return Player The player.
     */
    public function getActivePlayerForUser(ApplicationUserId $userId);

    /**
     * Gets the active player for the user and game type
     *
     * @param  ApplicationUserId $userId
     * @param  string            $gameType
     *
     * @return Player The player.
     */
    public function getActivePlayerForUserAndGameType(ApplicationUserId $userId, $gameType);

    /**
     * Gets the active player for the user and game type
     *
     * @param  ApplicationUserId $userId
     * @param  MiniGameId        $gameId
     *
     * @return Player The player.
     */
    public function getActivePlayerForUserAndGame(ApplicationUserId $userId, MiniGameId $gameId);

    /**
     * @param  Player $player
     *
     * @return void
     */
    public function save(Player $player);
}
