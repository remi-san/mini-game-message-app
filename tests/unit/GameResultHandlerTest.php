<?php
namespace MiniGameMessageApp\Test;

use League\Event\EventInterface;
use MessageApp\Finder\MessageFinder;
use MessageApp\Message;
use MessageApp\Message\Sender\MessageSender;
use MessageApp\SourceMessage;
use MessageApp\Test\Mock\MessageAppMocker;
use MessageApp\User\Finder\ContextUserFinder;
use MiniGame\Result\AllPlayersResult;
use MiniGame\Test\Mock\GameObjectMocker;
use MiniGameMessageApp\Handler\GameResultHandler;
use MiniGameMessageApp\Message\MiniGameMessageExtractor;
use MiniGameMessageApp\ReadModel\Finder\MiniGameUserFinder;
use MiniGameMessageApp\Test\Mock\AllResultEvent;
use Psr\Log\LoggerInterface;
use MiniGameMessageApp\Test\Mock\GameResultEvent;
use RemiSan\Context\Context;

class GameResultHandlerTest extends \PHPUnit_Framework_TestCase
{
    use MessageAppMocker, GameObjectMocker;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Init the mocks
     */
    public function setUp()
    {
        $this->userFinder = \Mockery::mock(MiniGameUserFinder::class);

        $this->contextUserFinder = \Mockery::mock(ContextUserFinder::class);

        $this->messageFinder = \Mockery::mock(MessageFinder::class);

        $this->messageSender = $this->getMessageSender();

        $this->extractor = \Mockery::mock(MiniGameMessageExtractor::class);

        $this->logger = \Mockery::mock('\\Psr\\Log\\LoggerInterface');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testUnsupportedEvent()
    {
        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');

        $this->userFinder->shouldReceive('getByPlayerId')->never();
        $this->messageSender->shouldReceive('send')->never();

        $event = \Mockery::mock(EventInterface::class);
        $listener->handle($event);
    }

    /**
     * @test
     */
    public function testIncompleteEvent()
    {
        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');

        $this->userFinder->shouldReceive('getByPlayerId')->never();
        $this->messageSender->shouldReceive('send')->never();

        $event = \Mockery::mock(GameResultEvent::class, function ($event) {
            $event->shouldReceive('getPlayerId')->andReturn(null);
            $event->shouldReceive('getName')->andReturn('name');
        });
        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($event, 'en')
            ->andReturn(null);

        $listener->handle($event);
    }

    /**
     * @test
     */
    public function testCompleteEvent()
    {
        $playerId = $this->getPlayerId(42);
        $user = $this->getApplicationUser($this->getApplicationUserId(33), 'Douglas');
        $user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        $messageText = 'toto';

        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');

        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($playerId)
            ->andReturn($user);

        $this->messageSender
            ->shouldReceive('send')
            ->with(
                \Mockery::on(function ($message) use ($user, $messageText) {
                    return $message instanceof Message\DefaultMessage &&
                        $message->getUser() == $user &&
                        $message->getMessage() == $messageText;
                }),
                null
            )
            ->once();

        $event = \Mockery::mock(GameResultEvent::class, function ($event) use ($playerId, $messageText) {
            $event->shouldReceive('getPlayerId')->andReturn($playerId);
            $event->shouldReceive('getName')->andReturn('name');
        });
        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($event, 'fr')
            ->andReturn($messageText);

        $listener->handle($event);
    }

    /**
     * @test
     */
    public function testCompleteEventWithContext()
    {
        $playerId = $this->getPlayerId(42);
        $user = $this->getApplicationUser($this->getApplicationUserId(33), 'Douglas');
        $user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        $messageText = 'toto';

        $contextMessage = \Mockery::mock(SourceMessage::class, function ($sm) {
            $sm->shouldReceive('getSource')->andReturn('sourceMessage');
        });

        $context = \Mockery::mock(Context::class, function ($context) {
            $context->shouldReceive('getValue')->andReturn('context');
        });

        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');

        $this->messageFinder
            ->shouldReceive('findByReference')
            ->with('context')
            ->andReturn($contextMessage);

        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn($user);

        $this->messageSender
            ->shouldReceive('send')
            ->with(
                \Mockery::on(function ($message) use ($user, $messageText) {
                    return $message instanceof Message\DefaultMessage &&
                    $message->getUser() == $user &&
                    $message->getMessage() == $messageText;
                }),
                'sourceMessage'
            )
            ->once();

        $event = \Mockery::mock(GameResultEvent::class, function ($event) use ($playerId, $messageText) {
            $event->shouldReceive('getPlayerId')->andReturn(null);
            $event->shouldReceive('getName')->andReturn('name');
        });
        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($event, 'fr')
            ->andReturn($messageText);

        $listener->handle($event, $context);
    }

    /**
     * @test
     */
    public function testCompleteEventWithContextAndNotFoundUser()
    {
        $playerId = $this->getPlayerId(42);
        $messageText = 'toto';

        $contextMessage = \Mockery::mock(SourceMessage::class, function ($sm) {
            $sm->shouldReceive('getSource')->andReturn('sourceMessage');
        });

        $context = \Mockery::mock(Context::class, function ($context) {
            $context->shouldReceive('getValue')->andReturn('context');
        });

        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');

        $this->messageFinder
            ->shouldReceive('findByReference')
            ->with('context')
            ->andReturn($contextMessage);

        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn(null);

        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($playerId)
            ->andReturn(null);

        $event = \Mockery::mock(GameResultEvent::class, function ($event) use ($playerId, $messageText) {
            $event->shouldReceive('getPlayerId')->andReturn($playerId);
            $event->shouldReceive('getName')->andReturn('name');
        });
        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($event, 'en')
            ->andReturn($messageText);

        $this->setExpectedException(\InvalidArgumentException::class);

        $listener->handle($event, $context);
    }

    /**
     * @test
     */
    public function testAllPlayersEvent()
    {
        $gameId = $this->getMiniGameId(42);
        $user = $this->getApplicationUser($this->getApplicationUserId(33), 'Douglas');
        $user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        $messageText = 'toto';

        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender,
            $this->extractor
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');

        $this->userFinder
            ->shouldReceive('getByGameId')
            ->with($gameId)
            ->andReturn([$user]);

        $this->messageSender
            ->shouldReceive('send')
            ->with(
                \Mockery::on(function ($message) use ($user, $messageText) {
                    return $message instanceof Message\DefaultMessage &&
                    $message->getUser() == $user &&
                    $message->getMessage() == $messageText;
                }),
                null
            )
            ->once();

        $event = \Mockery::mock(AllResultEvent::class, function ($event) use ($gameId, $messageText) {
            $event->shouldReceive('getGameId')->andReturn($gameId);
            $event->shouldReceive('getName')->andReturn('name');
        });
        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($event, 'fr')
            ->andReturn($messageText);

        $listener->handle($event);
    }
}
