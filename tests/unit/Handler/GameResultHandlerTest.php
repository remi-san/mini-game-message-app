<?php
namespace MiniGameMessageApp\Test\Handler;

use League\Event\EventInterface;
use MessageApp\Finder\MessageFinder;
use MessageApp\Message;
use MessageApp\Message\MessageFactory;
use MessageApp\Message\Sender\MessageSender;
use MessageApp\SourceMessage;
use MessageApp\User\ApplicationUser;
use MessageApp\User\ApplicationUserId;
use MessageApp\User\Finder\ContextUserFinder;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGameMessageApp\Finder\MiniGameUserFinder;
use MiniGameMessageApp\Handler\GameResultHandler;
use MiniGameMessageApp\Test\Mock\AllResultEvent;
use MiniGameMessageApp\Test\Mock\GameResultEvent;
use Mockery\Mock;
use RemiSan\Context\Context;

class GameResultHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlayerId */
    private $playerId;

    /** @var MiniGameId */
    private $gameId;

    /** @var ApplicationUser | Mock */
    private $user;

    /** @var SourceMessage | Mock */
    private $contextMessage;

    /** @var Context | Mock */
    private $context;

    /** @var EventInterface | Mock */
    private $event;

    /** @var MiniGameUserFinder | Mock */
    private $userFinder;

    /** @var ContextUserFinder | Mock */
    private $contextUserFinder;

    /** @var MessageFinder | Mock */
    private $messageFinder;

    /** @var MessageSender | Mock */
    private $messageSender;

    /** @var MessageFactory | Mock */
    private $messageFactory;

    /** @var GameResultHandler */
    private $serviceUnderTest;

    /**
     * Init the mocks
     */
    public function setUp()
    {
        $this->userFinder = \Mockery::mock(MiniGameUserFinder::class);
        $this->contextUserFinder = \Mockery::mock(ContextUserFinder::class);
        $this->messageFinder = \Mockery::mock(MessageFinder::class);
        $this->messageSender = \Mockery::mock(MessageSender::class);
        $this->messageFactory = \Mockery::mock(MessageFactory::class);

        $this->serviceUnderTest = new GameResultHandler(
            $this->userFinder,
            $this->contextUserFinder,
            $this->messageFinder,
            $this->messageFactory,
            $this->messageSender
        );
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
        $this->userFinder->shouldReceive('getByPlayerId')->never();
        $this->messageSender->shouldReceive('send')->never();

        $this->event = \Mockery::mock(EventInterface::class);
        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function testIncompleteEvent()
    {
        $this->userFinder->shouldReceive('getByPlayerId')->never();
        $this->messageSender->shouldReceive('send')->never();

        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn(null);
        $this->event->shouldReceive('getName')->andReturn('name');
            
        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([null], $this->event)
            ->andReturn(null);

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function testCompleteEvent()
    {
        $this->playerId = PlayerId::create(42);
        
        $this->user = \Mockery::mock(ApplicationUser::class);
        $this->user->shouldReceive('getId')->andReturn(new ApplicationUserId(33));
        $this->user->shouldReceive('getName')->andReturn('Douglas');
        $this->user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        
        $message = \Mockery::mock(Message::class);
        
        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($this->playerId)
            ->andReturn($this->user);

        $this->messageSender
            ->shouldReceive('send')
            ->with($message, null)
            ->once();

        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn($this->playerId);
        $this->event->shouldReceive('getName')->andReturn('name');
            
        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([$this->user], $this->event)
            ->andReturn($message);

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function testCompleteEventWithContext()
    {
        $this->user = \Mockery::mock(ApplicationUser::class);
        $this->user->shouldReceive('getId')->andReturn(new ApplicationUserId(33));
        $this->user->shouldReceive('getName')->andReturn('Douglas');
        $this->user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        
        $message = \Mockery::mock(Message::class);

        $this->contextMessage = \Mockery::mock(SourceMessage::class);
        $this->contextMessage->shouldReceive('getSource')->andReturn('sourceMessage');

        $this->context = \Mockery::mock(Context::class);
        $this->context->shouldReceive('getValue')->andReturn('context');
        
        $this->messageFinder
            ->shouldReceive('findByReference')
            ->with('context')
            ->andReturn($this->contextMessage);

        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn($this->user);

        $this->messageSender
            ->shouldReceive('send')
            ->with($message, 'sourceMessage')
            ->once();

        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn(null);
        $this->event->shouldReceive('getName')->andReturn('name');
            
        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([$this->user], $this->event)
            ->andReturn($message);

        $this->serviceUnderTest->handle($this->event, $this->context);
    }

    /**
     * @test
     */
    public function testCompleteEventWithContextAndNotFoundUser()
    {
        $this->playerId = PlayerId::create(42);

        $this->contextMessage = \Mockery::mock(SourceMessage::class);
        $this->contextMessage->shouldReceive('getSource')->andReturn('sourceMessage');

        $this->context = \Mockery::mock(Context::class);
        $this->context->shouldReceive('getValue')->andReturn('context');
        
        $this->messageFinder
            ->shouldReceive('findByReference')
            ->with('context')
            ->andReturn($this->contextMessage);

        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn(null);

        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($this->playerId)
            ->andReturn(null);

        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn($this->playerId);
        $this->event->shouldReceive('getName')->andReturn('name');

        $this->setExpectedException(\InvalidArgumentException::class);

        $this->serviceUnderTest->handle($this->event, $this->context);
    }

    /**
     * @test
     */
    public function testAllPlayersEvent()
    {
        $this->gameId = MiniGameId::create(42);
        
        $this->user = \Mockery::mock(ApplicationUser::class);
        $this->user->shouldReceive('getId')->andReturn(new ApplicationUserId(33));
        $this->user->shouldReceive('getName')->andReturn('Douglas');
        $this->user->shouldReceive('getPreferredLanguage')->andReturn('fr');
        
        $message = \Mockery::mock(Message::class);
        
        $this->userFinder
            ->shouldReceive('getByGameId')
            ->with($this->gameId)
            ->andReturn([$this->user]);

        $this->messageSender
            ->shouldReceive('send')
            ->with($message, null)
            ->once();

        $this->event = \Mockery::mock(AllResultEvent::class);
        $this->event->shouldReceive('getGameId')->andReturn($this->gameId);
        $this->event->shouldReceive('getName')->andReturn('name');

        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([$this->user], $this->event)
            ->andReturn($message);

        $this->serviceUnderTest->handle($this->event);
    }
}
