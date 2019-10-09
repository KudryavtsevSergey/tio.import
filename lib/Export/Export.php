<?php

namespace Tio\Import\Export;

abstract class Export
{
    public abstract function export(array $data);
}