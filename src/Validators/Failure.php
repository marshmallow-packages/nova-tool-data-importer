<?php

namespace Marshmallow\NovaDataImporter\Validators;

use Maatwebsite\Excel\Validators\Failure as BaseFailure;

class Failure extends BaseFailure
{
    public function toArray()
    {
        return [
            'row' => $this->row,
            'attribute' => $this->attribute,
            'errors' => $this->errors,
        ];
    }
}
