<?php

namespace EventLoadingSystemDesign\Interfaces;

interface EventSourceInterface
{
    public function getName(): string;

    public function fetchEvents(?int $lastEventId): array;
}