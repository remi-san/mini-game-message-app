<?php

namespace MiniGameMessageApp\Test;

use MessageApp\Event\UnableToCreateUserEvent;
use MessageApp\Event\UserEvent;
use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MessageApp\Parser\Exception\MessageParserException;
use MessageApp\User\ApplicationUser;
use MiniGame\GameResult;
use MiniGameMessageApp\Message\GameResultTextExtractor;

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
        $gameResult = \Mockery::mock(GameResult::class);
        $extractor = \Mockery::mock(MessageTextExtractor::class, function ($result) use ($message, $gameResult) {
            $result->shouldReceive('extractMessage')
                ->with($gameResult)
                ->andReturn($message)
                ->once();
        });

        $extractor = new GameResultTextExtractor([$extractor]);

        $extractedMessage = $extractor->extractMessage($gameResult);

        $this->assertEquals($message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithUnmanagedGameResult()
    {
        $gameResult = \Mockery::mock(GameResult::class);

        $extractor = new GameResultTextExtractor([]);

        $this->setExpectedException(\InvalidArgumentException::class);
        $extractor->extractMessage($gameResult);
    }

    /**
     * @test
     */
    public function testWithUnknownObject()
    {
        $extractor = new GameResultTextExtractor();

        $extractedMessage = $extractor->extractMessage(null);

        $this->assertNull($extractedMessage);
    }
}
