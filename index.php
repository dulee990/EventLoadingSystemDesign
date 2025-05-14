<?php

require __DIR__ . '/vendor/autoload.php';

use EventLoadingSystemDesign\Services\EventLoader;
use EventLoadingSystemDesign\Interfaces\{EventSourceInterface, EventStorageInterface};
use EventLoadingSystemDesign\Models\Event;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Psr\Log\AbstractLogger; 

class DummySource implements EventSourceInterface {
    public function getName(): string {
        return 'failing_source';
    }

    public function fetchEvents(?int $lastEventId): array {
      $start = $lastEventId ?? 0;
      return [
        new Event($start + 1, 'dummy', ['message' => 'Dusko']),
        new Event($start + 2, 'dummy', ['message' => 'Zivkovic']),
      ];
    }
}

class DummyStorage implements EventStorageInterface {
    private int $lastId = 0;

    public function store(array $events): void {
        echo "Stored " . count($events) . " event(s):\n";
        foreach ($events as $event) {
            echo "- {$event->id}: " . json_encode($event->payload) . "\n";
        }
    }

    public function getLastEventId(string $sourceName): ?int {
        return $this->lastId;
    }

    public function updateLastEventId(string $sourceName, int $lastEventId): void {
        $this->lastId = $lastEventId;
    }
}

class EchoLogger extends AbstractLogger {
    public function log($level, $message, array $context = []): void {
        echo strtoupper($level) . ': ' . $message . PHP_EOL;
    }
}

$lockFactory = new LockFactory(new FlockStore());
//$logger = new NullLogger();
$logger = new EchoLogger();

$loader = new EventLoader(
    [new DummySource()],
    new DummyStorage(),
    $lockFactory,
    $logger
);

$loader->load();
