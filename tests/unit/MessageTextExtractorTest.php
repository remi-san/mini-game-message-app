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
        $lang = 'en';
        $gameResult = \Mockery::mock(GameResult::class);
        $extractor = \Mockery::mock(MessageTextExtractor::class, function ($result) use ($message, $gameResult, $lang) {
            $result->shouldReceive('extractMessage')
                ->with($gameResult, $lang)
                ->andReturn($message)
                ->once();
        });

        $extractor = new GameResultTextExtractor([$extractor]);

        $extractedMessage = $extractor->extractMessage($gameResult, $lang);

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
        $extractor->extractMessage($gameResult, 'en');
    }

    /**
     * @test
     */
    public function testWithUnknownObject()
    {
        $extractor = new GameResultTextExtractor();

        $extractedMessage = $extractor->extractMessage(null, 'en');

        $this->assertNull($extractedMessage);
    }
}
