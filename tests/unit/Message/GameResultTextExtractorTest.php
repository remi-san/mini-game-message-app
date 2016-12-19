<?php

namespace MiniGameMessageApp\Test\Message;

use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MiniGame\GameResult;
use MiniGameMessageApp\Message\GameResultTextExtractor;
use Mockery\Mock;

class GameResultTextExtractorTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $message;

    /** @var GameResult */
    private $gameResult;

    /** @var MessageTextExtractor | Mock */
    private $innerExtractor;

    /** @var GameResultTextExtractor */
    private $extractor;

    public function setUp()
    {
        $this->message = 'test-message';
        $this->gameResult = \Mockery::mock(GameResult::class);
        $this->innerExtractor = \Mockery::mock(MessageTextExtractor::class);


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
        $this->innerExtractor->shouldReceive('extractMessage')
            ->with($this->gameResult)
            ->andReturn($this->message)
            ->once();

        $this->extractor = new GameResultTextExtractor([$this->innerExtractor]);

        $extractedMessage = $this->extractor->extractMessage($this->gameResult);

        $this->assertEquals($this->message, $extractedMessage);
    }

    /**
     * @test
     */
    public function testWithUnmanagedGameResult()
    {
        $this->gameResult = \Mockery::mock(GameResult::class);

        $this->extractor = new GameResultTextExtractor();

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->extractor->extractMessage($this->gameResult);
    }

    /**
     * @test
     */
    public function testWithUnknownObject()
    {
        $this->extractor = new GameResultTextExtractor();

        $extractedMessage = $this->extractor->extractMessage(null);

        $this->assertNull($extractedMessage);
    }
}
