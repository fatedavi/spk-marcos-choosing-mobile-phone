<?php

namespace App\Exports;

use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class FormulaValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && str_starts_with($value, '=')) {
            $cell->setValueExplicit($value, DataType::TYPE_FORMULA);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
