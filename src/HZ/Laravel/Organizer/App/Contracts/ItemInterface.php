<?php
namespace HZ\Laravel\Organizer\App\Contracts;

interface ItemInterface
{
    /**
     * Get the data that will be used in serializing
     * 
     * @return bool
     */
    public function send(): array;
}