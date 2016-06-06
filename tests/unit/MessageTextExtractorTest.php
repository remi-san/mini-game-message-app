<?php

namespace MiniGameMessageApp\Test;

use MessageApp\Event\UnableToCreateUserEvent;
use MessageApp\Event\UserEvent;
use MessageApp\Parser\Exception\MessageParserException;
use MessageApp\User\ApplicationUser;
use MiniGame\GameResult;
use MiniGameMessageApp\Message\MiniGameMessageTextExtractor;

class MessageTextExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testWithGameResult()
    {
        $message = 'test-message';
        $gameResult = \Mockery::mock(GameResult::class, function ($result) use ($message) {
            $result->shouldReceive('getAsMessage')->andReturn($message)->once();
        });

        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage($gameResult, 'en');

        $this->assertEquals($message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithUnableToCreateUserEvent()
    {
        $message = 'test-message';
        $event = \Mockery::mock(UnableToCreateUserEvent::class, function ($event) use ($message) {
            $event->shouldReceive('getReason')->andReturn($message)->once();
        });

        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage($event, 'en');

        $this->assertEquals($message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithUserEvent()
    {
        $message = 'test-message';
        $event = \Mockery::mock(UserEvent::class, function ($event) use ($message) {
            $event->shouldReceive('getAsMessage')->andReturn($message)->once();
        });

        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage($event, 'en');

        $this->assertEquals($message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithMessageParserException()
    {
        $message = 'test-message';
        $e = new MessageParserException(\Mockery::mock(ApplicationUser::class), $message);

        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage($e, 'en');

        $this->assertEquals($message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithUnknownObject()
    {
        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage(null, 'en');

        $this->assertNull($extractedMessage);
    }
}
