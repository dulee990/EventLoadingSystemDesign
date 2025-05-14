<?php

namespace EventLoadingSystemDesign\Models;

final class Event
{
  public function __construct(
    public readonly int $id,
    public readonly string $sourceName,
    public readonly array $payload,
  ) {}
}
