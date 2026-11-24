<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpkWorkbookImport implements WithMultipleSheets
{
    private SpkKriteriaImport $kriteria;

    private SpkDataImport $data;

    private SpkBobotImport $bobot;

    public function __construct()
    {
        $this->kriteria = new SpkKriteriaImport;
        $this->data = new SpkDataImport;
        $this->bobot = new SpkBobotImport;
    }

    public function sheets(): array
    {
        return [
            'kriteria' => $this->kriteria,
            'MARCOS' => $this->bobot,
            'Data' => $this->data,
        ];
    }

    public function criteria(): array
    {
        return $this->bobot->criteria();
    }

    public function subCriteria(): array
    {
        return $this->kriteria->subCriteria();
    }

    public function alternatives(): array
    {
        return $this->data->alternatives();
    }

    public function weights(): array
    {
        return $this->bobot->weights();
    }
}
