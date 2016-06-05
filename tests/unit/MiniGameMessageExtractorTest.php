<?php

namespace MiniGameMessageApp\Test;

use MiniGame\GameResult;
use MiniGameMessageApp\Message\MiniGameMessageExtractor;

class MiniGameMessageExtractorTest extends \PHPUnit_Framework_TestCase
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
    public function test()
    {
        $message = 'test-message';
        $gameResult = \Mockery::mock(GameResult::class, function ($result) use ($message) {
            $result->shouldReceive('getAsMessage')->andReturn($message)->once();
        });

        $extractor = new MiniGameMessageExtractor();

        $extractedMessage = $extractor->extractMessage($gameResult);

        $this->assertEquals($message, $extractedMessage);
    }
}
