<?php

namespace Tio\Import\Import;

use Tio\Import\Gateway\BusGateway;

abstract class Import
{
    /**
     * @var BusGateway
     */
    protected $gateway;

    public function __construct(BusGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public abstract function import(): array;
}