<?php
namespace MiniGameMessageApp\Test;

use League\Event\EventInterface;
use MessageApp\Finder\MessageFinder;
use MessageApp\Message;
use MessageApp\Message\Sender\MessageSender;
use MessageApp\Test\Mock\MessageAppMocker;
use MessageApp\User\Finder\ContextUserFinder;
use MiniGame\Test\Mock\GameObjectMocker;
use MiniGameMessageApp\Handler\GameResultHandler;
use MiniGameMessageApp\ReadModel\Finder\MiniGameUserFinder;
use Psr\Log\LoggerInterface;
use MiniGameMessageApp\Test\Mock\GameResultEvent;

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
            $this->messageSender
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
            $this->messageSender
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');

        $this->userFinder->shouldReceive('getByPlayerId')->never();
        $this->messageSender->shouldReceive('send')->never();

        $event = \Mockery::mock(GameResultEvent::class, function ($event) {
            $event->shouldReceive('getPlayerId')->andReturn(null);
            $event->shouldReceive('getAsMessage')->andReturn(null);
        });
        $listener->handle($event);
    }

    /**
     * @test
     */
    public function testCompleteEvent()
    {
        $playerId = $this->getPlayerId(42);
        $user = $this->getApplicationUser($this->getApplicationUserId(33), 'Douglas');
        $messageText = 'toto';

        $listener = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageSender
        );

        $listener->setLogger($this->logger);
        $this->logger->shouldReceive('info');

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
            $event->shouldReceive('getAsMessage')->andReturn($messageText);
        });
        $listener->handle($event);
    }
}
