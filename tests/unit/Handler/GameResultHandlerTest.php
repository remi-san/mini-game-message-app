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

    /** @var Message */
    private $message;

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
        $this->playerId = PlayerId::create(42);
        $this->gameId = MiniGameId::create(42);

        $this->user = \Mockery::mock(ApplicationUser::class);
        $this->message = \Mockery::mock(Message::class);
        $this->contextMessage = \Mockery::mock(SourceMessage::class);
        $this->context = \Mockery::mock(Context::class);

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
    public function itShouldNotHandleAnUnsupportedEvent()
    {
        $this->givenAnUnsupportedEvent();
        $this->givenThereIsNoContext();

        $this->assertUserWillNotBeRetrieved();
        $this->assertNoMessageWillBeSent();

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function itShouldNotHandleAnIncompleteEvent()
    {
        $this->givenAResultWithoutPlayer();
        $this->givenThereIsNoContext();

        $this->assertUserWillNotBeRetrieved();
        $this->assertNoMessageWillBeSent();

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function itShouldFailHandlingASimpleResultWithContextAndNotFoundUser()
    {
        $this->givenASimpleResult();
        $this->givenThereIsAContext();
        $this->givenUserCannotBeRetrieved();

        $this->assertNoMessageWillBeSent();

        $this->setExpectedException(\InvalidArgumentException::class);

        $this->serviceUnderTest->handle($this->event, $this->context);
    }

    /**
     * @test
     */
    public function itShouldHandleASimpleResultWithoutContext()
    {
        $this->givenASimpleResult();
        $this->givenThereIsNoContext();
        $this->givenUserCanBeRetrievedThroughResult();

        $this->assertMessageWillBeSentWithoutContext();

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function itShouldHandleAnIncompleteResultWithContext()
    {
        $this->givenAResultWithoutPlayer();
        $this->givenThereIsAContext();
        $this->givenUserCanBeRetrievedThroughContext();

        $this->assertMessageWillBeSentWithContext();

        $this->serviceUnderTest->handle($this->event, $this->context);
    }

    /**
     * @test
     */
    public function itShouldHandleAMultiplePlayersResult()
    {
        $this->givenAMultiplePlayersResult();
        $this->givenUsersCanBeRetrievedThroughTheGame();

        $this->assertMessageWillBeSentWithoutContext();

        $this->serviceUnderTest->handle($this->event);
    }

    private function initUser()
    {
        $this->user->shouldReceive('getId')->andReturn(new ApplicationUserId(33));
        $this->user->shouldReceive('getName')->andReturn('Douglas');
        $this->user->shouldReceive('getPreferredLanguage')->andReturn('fr');

        $this->givenAMessageCanBeBuilt();
    }

    private function messageCannotBeBuilt()
    {
        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([null], $this->event)
            ->andReturn(null);
    }

    private function givenAnUnsupportedEvent()
    {
        $this->event = \Mockery::mock(EventInterface::class);
    }

    private function givenAResultWithoutPlayer()
    {
        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn(null);
        $this->event->shouldReceive('getName')->andReturn('name');

        $this->messageCannotBeBuilt();
    }

    private function givenAMultiplePlayersResult()
    {
        $this->event = \Mockery::mock(AllResultEvent::class);
        $this->event->shouldReceive('getGameId')->andReturn($this->gameId);
        $this->event->shouldReceive('getName')->andReturn('name');
    }

    private function givenThereIsNoContext()
    {
    }

    private function givenASimpleResult()
    {
        $this->event = \Mockery::mock(GameResultEvent::class);
        $this->event->shouldReceive('getPlayerId')->andReturn($this->playerId);
        $this->event->shouldReceive('getName')->andReturn('name');
    }

    private function givenThereIsAContext()
    {
        $this->contextMessage->shouldReceive('getSource')->andReturn('sourceMessage');
        $this->context->shouldReceive('getValue')->andReturn('context');

        $this->messageFinder
            ->shouldReceive('findByReference')
            ->with('context')
            ->andReturn($this->contextMessage);
    }

    private function givenAMessageCanBeBuilt()
    {
        $this->messageFactory
            ->shouldReceive('buildMessage')
            ->with([$this->user], $this->event)
            ->andReturn($this->message);
    }

    private function givenUserCanBeRetrievedThroughResult()
    {
        $this->initUser();

        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($this->playerId)
            ->andReturn($this->user);
    }

    private function givenUserCanBeRetrievedThroughContext()
    {
        $this->initUser();

        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn($this->user);
    }

    private function givenUsersCanBeRetrievedThroughTheGame()
    {
        $this->initUser();

        $this->userFinder
            ->shouldReceive('getByGameId')
            ->with($this->gameId)
            ->andReturn([$this->user]);
    }

    private function givenUserCannotBeRetrieved()
    {
        $this->contextUserFinder
            ->shouldReceive('getUserByContextMessage')
            ->with('sourceMessage')
            ->andReturn(null);

        $this->userFinder
            ->shouldReceive('getByPlayerId')
            ->with($this->playerId)
            ->andReturn(null);
    }

    private function assertUserWillNotBeRetrieved()
    {
        $this->userFinder->shouldReceive('getByPlayerId')->never();
    }

    private function assertNoMessageWillBeSent()
    {
        $this->messageSender->shouldReceive('send')->never();
    }

    private function assertMessageWillBeSentWithoutContext()
    {
        $this->messageSender
            ->shouldReceive('send')
            ->with($this->message, null)
            ->once();
    }

    private function assertMessageWillBeSentWithContext()
    {
        $this->messageSender
            ->shouldReceive('send')
            ->with($this->message, 'sourceMessage')
            ->once();
    }
}
