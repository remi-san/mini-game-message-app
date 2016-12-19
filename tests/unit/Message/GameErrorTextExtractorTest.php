<?php

namespace MiniGameMessageApp\Test\Message;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGameApp\Event\MiniGameAppErrorEvent;
use MiniGameMessageApp\Message\GameErrorTextExtractor;
use RemiSan\Intl\TranslatableResource;

class GameErrorTextExtractorTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $message;

    /** @var MiniGameAppErrorEvent */
    private $gameResult;

    /** @var GameErrorTextExtractor */
    private $extractor;

    public function setUp()
    {
        $this->gameResult = new MiniGameAppErrorEvent(
            MiniGameId::create(),
            PlayerId::create(),
            $this->message
        );

        $this->extractor = new GameErrorTextExtractor();
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldExtractMessageIfMiniGameAppErrorEvent()
    {
        $this->givenAMiniGameAppErrorEvent();

        $extractedMessage = $this->extractor->extractMessage($this->gameResult);

        $this->assertEquals(new TranslatableResource($this->message), $extractedMessage);
    }

    /**
     * @test
     */
    public function itShouldNotExtractMessageIfNotAMiniGameAppErrorEvent()
    {
        $extractedMessage = $this->extractor->extractMessage(null);

        $this->assertNull($extractedMessage);
    }

    private function givenAMiniGameAppErrorEvent()
    {
    }
}
