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

        $this->extractor = new GameResultTextExtractor([$this->innerExtractor]);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldExtractMessageIfInnerExtractorsCan()
    {
        $this->givenInnerExtractorCanExtractMessage();

        $extractedMessage = $this->extractor->extractMessage($this->gameResult);

        $this->assertEquals($this->message, $extractedMessage);
    }

    /**
     * @test
     */
    public function itShouldFailExtractingMessageIfInnerExtractorsCannot()
    {
        $this->givenInnerExtractorCannotExtractMessage();

        $this->setExpectedException(\InvalidArgumentException::class);

        $this->extractor->extractMessage($this->gameResult);
    }

    /**
     * @test
     */
    public function itShouldNotExtractMessageIfNotAGameResult()
    {
        $extractedMessage = $this->extractor->extractMessage(null);

        $this->assertNull($extractedMessage);
    }

    private function givenInnerExtractorCanExtractMessage()
    {
        $this->innerExtractor->shouldReceive('extractMessage')
            ->with($this->gameResult)
            ->andReturn($this->message);
    }

    private function givenInnerExtractorCannotExtractMessage()
    {
        $this->innerExtractor->shouldReceive('extractMessage')
            ->with($this->gameResult)
            ->andReturn(null);
    }
}
