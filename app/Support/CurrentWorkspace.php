<?php

namespace App\Support;

class CurrentWorkspace
{
    private ?int $id = null;

    public function set(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function clear(): void
    {
        $this->id = null;
    }
}
