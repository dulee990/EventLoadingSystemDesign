<?php

namespace EventLoadingSystemDesign\Tests;

use PHPUnit\Framework\TestCase;
use EventLoadingSystemDesign\Services\EventLoader;
use EventLoadingSystemDesign\Interfaces\EventSourceInterface;
use EventLoadingSystemDesign\Interfaces\EventStorageInterface;
use EventLoadingSystemDesign\Models\Event;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Psr\Log\NullLogger;

class EventLoaderTest extends TestCase
{
    public function testEventSourceReturnsMaximum1000Events()
    {
        $events = [];
        for ($i = 1; $i <= 1000; $i++) {
            $events[] = new Event($i, 'test_source', ['msg' => "event_$i"]);
        }

        $source = $this->createMock(EventSourceInterface::class);
        $source->method('getName')->willReturn('test_source');
        $source->expects($this->once())
               ->method('fetchEvents')
               ->willReturn($events);

        $storage = $this->createMock(EventStorageInterface::class);
        $storage->method('getLastEventId')->willReturn(null);
        $storage->expects($this->once())
                ->method('store')
                ->with($this->callback(function ($passedEvents) {
                    return count($passedEvents) === 1000;
                }));
        $storage->method('updateLastEventId');

        $lockFactory = new LockFactory(new FlockStore());
        $loader = new EventLoader([$source], $storage, $lockFactory, new NullLogger());

        $loader->load();
    }
}
