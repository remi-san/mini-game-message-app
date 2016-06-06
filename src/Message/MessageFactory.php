<?php

namespace MiniGameMessageApp\Message;

use MessageApp\Message\DefaultMessage;
use MessageApp\User\ApplicationUser;
use MessageApp\User\UndefinedApplicationUser;

class MessageFactory
{
    /**
     * @var MessageTextExtractor
     */
    private $extractor;

    /**
     * Constructor.
     *
     * @param MessageTextExtractor $extractor
     */
    public function __construct(MessageTextExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @param  ApplicationUser[] $users
     * @param  object            $object
     * @param  string            $language
     * @return DefaultMessage
     */
    public function buildMessage(array $users, $object, $language = null)
    {
        $filteredUsers = self::filterUsers($users);

        if (count($filteredUsers) === 0) {
            return null;
        }

        $messageText = $this->extractor->extractMessage($object, ($language) ? : self::getLanguage($filteredUsers));

        if ($messageText === null) {
            return null;
        }

        return new DefaultMessage($filteredUsers, $messageText);
    }

    /**
     * @param  ApplicationUser[] $users
     * @return ApplicationUser[]
     */
    private static function filterUsers(array $users)
    {
        return array_values(
            array_unique(
                array_filter($users, function (ApplicationUser $user = null) {
                    return $user !== null && !$user instanceof UndefinedApplicationUser;
                })
            )
        );
    }

    /**
     * @param  ApplicationUser[] $users
     * @return string
     */
    private static function getLanguage(array $users)
    {
        return $users[0]->getPreferredLanguage();
    }
}
