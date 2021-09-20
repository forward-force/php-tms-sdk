<?php

namespace ForwardForce\TMS\Contracts;

interface ApiAwareContract
{
    /**
     * Get all of entity
     *
     * @return array
     */
    public function fetch(): array;
}
