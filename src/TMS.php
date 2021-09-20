<?php

namespace ForwardForce\TMS;

use ForwardForce\TMS\Entities\Lineup;
use SebastianBergmann\Diff\Line;

class TMS
{
    /**
     * RightSignature API key
     *
     * @var string
     */
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Fetch documents from rightSignature.com
     *
     * @return Lineup
     */
    public function lineups(): Lineup
    {
        return new Lineup($this->token);
    }
}
