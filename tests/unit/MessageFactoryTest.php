<?php

namespace MiniGameMessageApp\Test;

use MessageApp\User\ApplicationUser;
use MessageApp\User\UndefinedApplicationUser;
use MiniGameMessageApp\Message\MessageFactory;
use MiniGameMessageApp\Message\MessageTextExtractor;

class MessageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageTextExtractor
     */
    private $extractor;

    public function setUp()
    {
        $this->extractor = \Mockery::mock(MessageTextExtractor::class);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function withNoUsersItShouldReturnNull()
    {
        $factory = new MessageFactory($this->extractor);

        $this->assertNull($factory->buildMessage([], null));
    }

    /**
     * @test
     */
    public function withNullUserItShouldReturnNull()
    {
        $factory = new MessageFactory($this->extractor);

        $this->assertNull($factory->buildMessage([null], null));
    }

    /**
     * @test
     */
    public function withUndefinedApplicationUserItShouldReturnNull()
    {
        $factory = new MessageFactory($this->extractor);

        $this->assertNull($factory->buildMessage([\Mockery::mock(UndefinedApplicationUser::class)], null));
    }

    /**
     * @test
     */
    public function withApplicationUserIfItCannotExtractMessageItShouldReturnNull()
    {
        $language = 'en';
        $user = \Mockery::mock(ApplicationUser::class);
        $object = new \stdClass();

        $factory = new MessageFactory($this->extractor);

        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($object, $language)
            ->andReturn(null);

        $this->assertNull($factory->buildMessage([$user], $object, $language));
    }

    /**
     * @test
     */
    public function withApplicationUserIfLanguageIsPassedMessageItShouldReturnMessageTranslatedInRequiredLanguage()
    {
        $language = 'en';
        $user = \Mockery::mock(ApplicationUser::class);
        $object = new \stdClass();
        $translatedMessage = 'translated';

        $factory = new MessageFactory($this->extractor);

        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($object, $language)
            ->andReturn($translatedMessage);

        $message = $factory->buildMessage([$user, null], $object, $language);

        $this->assertEquals([$user], $message->getUsers());
        $this->assertEquals($translatedMessage, $message->getMessage());
    }

    /**
     * @test
     */
    public function withApplicationUserIfLanguageIsNotPassedMessageItShouldReturnMessageTranslatedInUserLanguage()
    {
        $language = 'en';
        $user = \Mockery::mock(ApplicationUser::class, function ($u) use ($language) {
            $u->shouldReceive('getPreferredLanguage')->andReturn($language);
        });
        $object = new \stdClass();
        $translatedMessage = 'translated';

        $factory = new MessageFactory($this->extractor);

        $this->extractor
            ->shouldReceive('extractMessage')
            ->with($object, $language)
            ->andReturn($translatedMessage);

        $message = $factory->buildMessage([null, $user], $object);

        $this->assertEquals([$user], $message->getUsers());
        $this->assertEquals($translatedMessage, $message->getMessage());
    }
}
