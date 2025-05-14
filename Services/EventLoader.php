<?php

namespace EventLoadingSystemDesign\Services;
 
use Symfony\Component\Lock\LockFactory as LockFactoryInterface;
use Psr\Log\LoggerInterface;
use EventLoadingSystemDesign\Models\Exceptions\EventSourceUnavailableException;
use EventLoadingSystemDesign\Interfaces\EventLoaderInterface;
use EventLoadingSystemDesign\Interfaces\EventStorageInterface;


class EventLoader implements EventLoaderInterface
{
    public function __construct(
      private readonly iterable $sources,
      private readonly EventStorageInterface $storage,
      private readonly LockFactoryInterface $lockFactory,
      private readonly LoggerInterface $logger,
    ) {}

    public function load(): void
    {
      foreach ($this->sources as $source) {
        $lock = $this->lockFactory->createLock('event_loader_' . $source->getName());

        if (!$lock->acquire()) {
          $this->logger->info("Could not acquire lock for source: " . $source->getName());
          continue;
        }

        try {
          $lastId = $this->storage->getLastEventId($source->getName());
          $events = $source->fetchEvents($lastId);

          if ($events) {
            $this->storage->store($events);
            $lastEvent = end($events);
            $this->storage->updateLastEventId($source->getName(), $lastEvent->id);
          }

          usleep(200_000); // 200ms
        }
        catch (EventSourceUnavailableException $e) {
          $this->logger->warning("Source unavailable: " . $source->getName());
        }
        finally {
          $lock->release();
        }
      }
    }
}
