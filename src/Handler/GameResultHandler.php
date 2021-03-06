<?php

namespace MiniGameMessageApp\Handler;

use League\Event\EventInterface;
use MessageApp\Finder\MessageFinder;
use MessageApp\Listener\MessageEventHandler;
use MessageApp\Message\MessageFactory;
use MessageApp\Message\Sender\MessageSender;
use MessageApp\User\ApplicationUser;
use MessageApp\User\Finder\ContextUserFinder;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;
use MiniGame\Result\AllPlayersResult;
use MiniGameMessageApp\Finder\MiniGameUserFinder;
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
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var MessageSender
     */
    private $messageSender;

    /**
     * Constructor
     *
     * @param MiniGameUserFinder $userFinder
     * @param ContextUserFinder  $contextUserFinder
     * @param MessageFinder      $messageFinder
     * @param MessageFactory     $messageFactory
     * @param MessageSender      $messageSender
     */
    public function __construct(
        MiniGameUserFinder $userFinder,
        ContextUserFinder $contextUserFinder,
        MessageFinder $messageFinder,
        MessageFactory $messageFactory,
        MessageSender $messageSender
    ) {
        $this->userFinder = $userFinder;
        $this->contextUserFinder = $contextUserFinder;
        $this->messageFinder = $messageFinder;
        $this->messageFactory = $messageFactory;
        $this->messageSender = $messageSender;
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

        $users = $this->getUsersList($event, $messageContext);

        $this->sendMessage($event, $users, $messageContext);
    }

    /**
     * @param  GameResult        $gameResult
     * @param  ApplicationUser[] $users
     * @param  mixed             $messageContext
     * @return void
     */
    private function sendMessage(GameResult $gameResult, array $users = [], $messageContext = null)
    {
        $message = $this->messageFactory->buildMessage($users, $gameResult);

        if (!$message) {
            $this->logger->warning('Message could not be generated');
            return;
        }

        $this->messageSender->send($message, $messageContext);
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

    /**
     * @param GameResult $event
     * @param mixed      $messageContext
     *
     * @return ApplicationUser[]
     */
    private function getUsersList(GameResult $event, $messageContext)
    {
        if ($event instanceof AllPlayersResult) {
            return $this->userFinder->getByGameId($event->getGameId());
        }

        return [ $this->getUser($event->getPlayerId(), $messageContext) ];
    }
}
