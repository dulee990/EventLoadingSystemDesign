<?php

namespace EventLoadingSystemDesign\Interfaces;

interface EventStorageInterface
{
    public function store(array $events): void;

    public function getLastEventId(string $sourceName): ?int;

    public function updateLastEventId(string $sourceName, int $lastEventId): void;
}