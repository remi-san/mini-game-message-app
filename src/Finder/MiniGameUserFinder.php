<?php

namespace MiniGameMessageApp\Finder;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGameMessageApp\MiniGameApplicationUser;

interface MiniGameUserFinder
{
    /**
     * Finds an user by its primary key / identifier.
     *
     * @param  string $id The identifier.
     *
     * @return MiniGameApplicationUser The user.
     */
    public function find($id);

    /**
     * @param  MiniGameApplicationUser $user
     * @return void
     */
    public function save(MiniGameApplicationUser $user);

    /**
     * Gets a user by a player id
     *
     * @param PlayerId $playerId
     *
     * @return MiniGameApplicationUser
     */
    public function getByPlayerId(PlayerId $playerId);

    /**
     * Gets users by a game id
     *
     * @param MiniGameId $gameId
     *
     * @return MiniGameApplicationUser[]
     */
    public function getByGameId(MiniGameId $gameId);
}
