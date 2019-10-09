<?php

namespace Tio\Import;

use Tio\Import\Export\Export;
use Tio\Import\Export\IBlockExport;
use Tio\Import\Gateway\BelOrientirGateway;
use Tio\Import\Import\BelOrientirImport;
use Tio\Import\Import\Import;

/**
 * Tools class
 */
class Tools
{
    public function importBelOrientir()
    {
        $gateway = new BelOrientirGateway;
        $import = new BelOrientirImport($gateway);
        $export = new IBlockExport();

        $this->importWithExport($import, $export);
    }

    private function importWithExport(Import $import, Export $export)
    {
        $data = $import->import();

        $export->export($data);
    }
}
