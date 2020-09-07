<?php


namespace App\Taxes\Event;


interface EventInterface
{
    public function stopPropagation(): void;

    public function isStopped(): bool;
}