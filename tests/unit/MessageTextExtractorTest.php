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
    public function testWithUnknownObject()
    {
        $extractor = new MiniGameMessageTextExtractor();

        $extractedMessage = $extractor->extractMessage(null, 'en');

        $this->assertNull($extractedMessage);
    }
}
