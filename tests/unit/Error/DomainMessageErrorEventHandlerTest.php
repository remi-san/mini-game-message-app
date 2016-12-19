<?php

namespace MiniGameMessageApp\Test\Error;

use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBusInterface;
use League\Event\EventInterface;
use MiniGameMessageApp\Error\DomainMessageErrorEventHandler;
use Mockery\Mock;

class DomainMessageErrorEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventBusInterface | Mock */
    private $eventBus;

    /** @var EventInterface */
    private $error;

    /** @var DomainMessageErrorEventHandler */
    private $handler;

    public function setUp()
    {
        $this->error = \Mockery::mock(EventInterface::class);
        $this->eventBus = \Mockery::mock(EventBusInterface::class);

        $this->handler = new DomainMessageErrorEventHandler($this->eventBus);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldHandleTheError()
    {
        $this->assertErrorWillBePublishedAsAnEvent();

        $this->handler->handle($this->error);
    }

    private function assertErrorWillBePublishedAsAnEvent()
    {
        $this->eventBus->shouldReceive('publish')
            ->with(\Mockery::on(function (DomainEventStream $eventStream) {
                $this->assertInstanceOf(DomainEventStream::class, $eventStream);

                $streamIterator = $eventStream->getIterator();
                $this->assertEquals(1, count($streamIterator));

                $message = $streamIterator[0];
                $this->assertEquals($this->error, $message->getPayload());

                return true;
            }))->once();
    }
}
