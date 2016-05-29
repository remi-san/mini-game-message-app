<?php

namespace MiniGameMessageApp\Test;

use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBusInterface;
use League\Event\EventInterface;
use MiniGameMessageApp\Error\DomainMessageErrorEventHandler;

class DomainMessageErrorEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

    public function setUp()
    {
        $this->eventBus = \Mockery::mock(EventBusInterface::class);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function handleShouldPublishADomainMessage()
    {
        $error = \Mockery::mock(EventInterface::class);

        $this->eventBus->shouldReceive('publish')->with(\Mockery::on(function (DomainEventStream $eventStream) use ($error) {
            $this->assertInstanceOf(DomainEventStream::class, $eventStream);

            $streamIterator = $eventStream->getIterator();
            $this->assertEquals(1, count($streamIterator));

            $message = $streamIterator[0];
            $this->assertEquals($error, $message->getPayload());

            return true;
        }))->once();

        $handler = new DomainMessageErrorEventHandler($this->eventBus);
        $handler->handle($error);
    }
}
