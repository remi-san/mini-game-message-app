<?php

namespace MiniGameMessageApp\Test\Listener;

use League\Event\EventInterface;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGameMessageApp\Store\MiniGameUserStore;
use MiniGameMessageApp\Listener\ApplicationUserListener;
use MiniGameMessageApp\PersistableMiniGameUser;
use MiniGameMessageApp\Test\Mock\MiniGamePlayerCreatedEvent;
use Mockery\Mock;

class ApplicationUserListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventInterface | Mock */
    private $event;

    /** @var string */
    private $extRef;

    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var PersistableMiniGameUser | Mock */
    private $user;

    /** @var MiniGameUserStore | Mock */
    private $store;

    /** @var ApplicationUserListener */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $this->extRef = 'externalReference';
        $this->gameId = MiniGameId::create();
        $this->playerId = PlayerId::create();

        $this->user = \Mockery::mock(PersistableMiniGameUser::class);

        $this->store = \Mockery::mock(MiniGameUserStore::class);

        $this->serviceUnderTest = new ApplicationUserListener($this->store);
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBeAListener()
    {
        $this->assertTrue($this->serviceUnderTest->isListener($this->serviceUnderTest));
    }

    /**
     * @test
     */
    public function itShouldNotBeAListener()
    {
        $this->assertFalse($this->serviceUnderTest->isListener(new \stdClass()));
    }

    /**
     * @test
     */
    public function itShouldNotHandleOtherThanPlayerCreatedEvent()
    {
        $this->givenARandomEvent();

        $this->assertUserWillNotBeModified();

        $this->serviceUnderTest->handle($this->event);
    }

    /**
     * @test
     */
    public function itShouldHandlePlayerCreatedEvent()
    {
        $this->givenAPlayerCreatedEvent();

        $this->assertUserWillBeModified();

        $this->serviceUnderTest->handle($this->event);
    }

    private function givenARandomEvent()
    {
        $this->event = \Mockery::mock(EventInterface::class);
    }

    private function givenAPlayerCreatedEvent()
    {
        $this->event = \Mockery::mock(MiniGamePlayerCreatedEvent::class);
        $this->event->shouldReceive('getExternalReference')->andReturn($this->extRef);
        $this->event->shouldReceive('getGameId')->andReturn($this->gameId);
        $this->event->shouldReceive('getPlayerId')->andReturn($this->playerId);
    }

    private function assertUserWillNotBeModified()
    {
        $this->store
            ->shouldReceive('save')
            ->never();
    }

    private function assertUserWillBeModified()
    {
        $this->store
            ->shouldReceive('find')
            ->with($this->extRef)
            ->andReturn($this->user);

        $this->user
            ->shouldReceive('linkToPlayer')
            ->with($this->gameId, $this->playerId)
            ->once();

        $this->store
            ->shouldReceive('save')
            ->with($this->user)
            ->once();
    }
}
