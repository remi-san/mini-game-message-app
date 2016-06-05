<?php

namespace MiniGameMessageApp\Handler;

use League\Event\EventInterface;
use MessageApp\Finder\MessageFinder;
use MessageApp\Listener\MessageEventHandler;
use MessageApp\Message\DefaultMessage;
use MessageApp\Message\Sender\MessageSender;
use MessageApp\User\ApplicationUser;
use MessageApp\User\Finder\ContextUserFinder;
use MessageApp\User\UndefinedApplicationUser;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;
use MiniGame\Result\AllPlayersResult;
use MiniGameMessageApp\Message\MiniGameMessageExtractor;
use MiniGameMessageApp\ReadModel\Finder\MiniGameUserFinder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RemiSan\Context\Context;

class GameResultHandler implements MessageEventHandler, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MiniGameUserFinder
     */
    private $userFinder;

    /**
     * @var ContextUserFinder
     */
    private $contextUserFinder;

    /**
     * @var MessageFinder
     */
    private $messageFinder;

    /**
     * @var MessageSender
     */
    private $messageSender;

    /**
     * @var MiniGameMessageExtractor
     */
    private $extractor;

    /**
     * Constructor
     *
     * @param MiniGameUserFinder       $userFinder
     * @param ContextUserFinder        $contextUserFinder
     * @param MessageFinder            $messageFinder
     * @param MessageSender            $messageSender
     * @param MiniGameMessageExtractor $extractor
     */
    public function __construct(
        MiniGameUserFinder $userFinder,
        ContextUserFinder $contextUserFinder,
        MessageFinder $messageFinder,
        MessageSender $messageSender,
        MiniGameMessageExtractor $extractor
    ) {
        $this->userFinder = $userFinder;
        $this->contextUserFinder = $contextUserFinder;
        $this->messageFinder = $messageFinder;
        $this->messageSender = $messageSender;
        $this->extractor = $extractor;
        $this->logger = new NullLogger();
    }

    /**
     * Handle an event.
     *
     * @param EventInterface $event
     * @param Context        $context
     *
     * @return void
     */
    public function handle(EventInterface $event, Context $context = null)
    {
        if (! $event instanceof GameResult) {
            return;
        }

        $this->logger->info(sprintf('Send message after "%s"', $event->getName()));
        $messageContext = $this->getMessageContext($context);

        if ($event instanceof AllPlayersResult) {
            $users = $this->userFinder->getByGameId($event->getGameId());
        } else {
            $users = [ $this->getUser($event->getPlayerId(), $messageContext) ];
        }

        $this->sendMessage($event, $users, $messageContext);
    }

    /**
     * @param  GameResult        $gameResult
     * @param  ApplicationUser[] $users
     * @param  mixed             $messageContext
     * @return void
     */
    private function sendMessage(GameResult $gameResult, array $users = array(), $messageContext = null)
    {
        $filteredUsers = self::filterUsers($users);

        if (count($filteredUsers) === 0) {
            return;
        }

        $message = new DefaultMessage(
            $filteredUsers,
            $this->extractor->extractMessage(
                $gameResult,
                self::getLanguage($filteredUsers)
            )
        );
        $this->messageSender->send($message, $messageContext);
    }

    /**
     * @param  ApplicationUser[] $users
     * @return ApplicationUser[]
     */
    private static function filterUsers(array $users)
    {
        return array_unique(
            array_filter($users, function (ApplicationUser $user = null) {
                return $user !== null && !$user instanceof UndefinedApplicationUser;
            })
        );
    }

    /**
     * @param  ApplicationUser[] $users
     * @return string
     */
    private static function getLanguage(array $users)
    {
        return $users[0]->getPreferredLanguage(); // TODO add better language management
    }

    /**
     * @param  Context $context
     * @return mixed
     */
    private function getMessageContext(Context $context = null)
    {
        if (!$context) {
            return null;
        }

        $message = $this->messageFinder->findByReference($context->getValue());
        return ($message) ? $message->getSource() : null;
    }

    /**
     * @param  PlayerId $playerId
     * @param  mixed    $contextMessage
     * @return ApplicationUser
     */
    private function getUser(PlayerId $playerId = null, $contextMessage = null)
    {
        // Build message
        $user = $this->getUserByPlayerId($playerId);
        if (!$user) {
            $this->logger->debug('Try to get user by context message');
            $user = $this->getUserByContext($contextMessage);
            if (!$user) {
                if (!$playerId) {
                    $this->logger->debug('No user was found');
                    return null;
                }
                throw new \InvalidArgumentException('User not found!');
            }
        }

        return $user;
    }

    /**
     * @param  PlayerId $id
     * @return ApplicationUser
     */
    private function getUserByPlayerId(PlayerId $id = null)
    {
        return ($id) ? $this->userFinder->getByPlayerId($id) : null;
    }

    /**
     * @param  mixed $contextMessage
     * @return ApplicationUser
     */
    private function getUserByContext($contextMessage = null)
    {
        if (!$contextMessage) {
            $this->logger->debug('No context message to retrieve user from');
            return null;
        };

        return $this->contextUserFinder->getUserByContextMessage($contextMessage);
    }
}
