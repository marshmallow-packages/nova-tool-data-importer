<?php

namespace Marshmallow\NovaDataImporter\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;
use Marshmallow\NovaDataImporter\Validators\Failure;

trait MarshmallowImporterTrait
{
    protected $request;

    protected $row_counter = 0;

    protected $unique;

    protected $file;

    protected $job;

    protected $pdoFailures = [];

    public function setFile($file)
    {
        $this->file = $file;
        $this->job = MarshmallowNovaImportJob::where('file', $file)->get()->first();

        return $this;
    }

    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    protected function triggerProgressUpdate()
    {
        $this->job->progress = $this->row_counter;
        $this->job->update();
    }

    private function mapRowDataToAttributes($row)
    {
        $data = [];

        foreach ($this->attributes as $field) {
            $data[$field] = null;

            foreach ($this->attribute_map as $column => $attribute) {
                if (! isset($row[$column]) || $field !== $attribute) {
                    continue;
                }
                $data[$field] = $this->preProcessValue($field, $row[$column]);
            }
        }

        return $data;
    }

    private function preProcessValue($field, $value)
    {
        switch ($value) {
            case 'FALSE':
                return false;

                break;
            case 'TRUE':
                return true;

                break;
        }

        switch (strtolower($field)) {
            case 'password':
                return bcrypt($value);

                break;
        }

        return $value;
    }

    /**
     * Deze functie zorgt ervoor dat als er een unieke
     * kolom mee gegeven is, dat we het ID van de meegeven
     * model in de regel toevoegen, zodat de validator snapt
     * dat het unieke veld wel mag bestaan voor deze model.
     * @return [type] [description]
     */
    protected function buildValidationRules(Model $model = null)
    {
        $rules = $this->rules;
        if ($model && $this->unique) {
            $unique_column = $this->unique;
            if (isset($rules[$unique_column])) {
                foreach ($rules[$unique_column] as $k => $rule) {
                    if (strpos($rule, 'unique') === false) {
                        continue;
                    }
                    $rules[$unique_column][$k] .= ',' . $model->id;
                }
            }
        }

        return $rules;
    }

    public function validateRow($row, Model $model = null)
    {
        $this->row_counter++;

        try {
            validator()->make($row, $this->buildValidationRules($model))->validate();
        } catch (ValidationException $e) {
            $failures = [];
            foreach ($e->errors() as $attributeName => $messages) {
                $failures[] = new Failure(
                    $this->row_counter,
                    $attributeName,
                    $messages,
                    $row
                );
            }

            if ($this instanceof SkipsOnFailure) {
                $this->onFailure(...$failures);

                throw new RowSkippedException(...$failures);
            }

            throw new \Maatwebsite\Excel\Validators\ValidationException(
                $e,
                $failures
            );
        }
    }

    public function rules(): array
    {
        return [];
    }
}
