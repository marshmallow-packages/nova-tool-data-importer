<?php

namespace Marshmallow\NovaDataImporter;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Marshmallow\NovaDataImporter\Traits\MarshmallowImporterTrait;

class Importer implements ToCollection, WithValidation, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsOnFailure, SkipsOnError, WithCustomCsvSettings
{
    use Importable, SkipsFailures, SkipsErrors, MarshmallowImporterTrait;

    /** @var Resource */
    protected $resource;

    protected $attributes;

    protected $attribute_map;

    protected $rules;

    protected $model_class;

    protected $rows_handled = 0;

    protected $current_row = 0;

    protected function updateProgress()
    {
        $this->job->progress = $this->looped;
        $this->job->update();
    }

    protected function rowAlreadyHandled()
    {
        return ($this->current_row < $this->job->progress);
    }

    protected $looped = 0;

    protected $handled = 0;

    protected $start = 0;

    public function prepare()
    {
        $this->start = $this->job->progress + 1;

        return $this;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $current_row_number => $row) {
            $this->looped++;
            if ($this->looped < $this->start) {
                continue;
            }
            $row = $this->mapRowDataToAttributes($row);

            try {
                if (
                    $this->unique
                    && $model = $this->resource->resource::where($this->unique, $row[$this->unique])->get()->first()) {
                    $this->validateRow($row, $model);
                    $model->update($row);
                } else {
                    $this->validateRow($row);
                    $this->resource->resource::create($row);
                }
            } catch (\PDOException $e) {
                $failures = [];

                if (! in_array('PDOException', $this->pdoFailures)) {
                    $this->pdoFailures[] = 'PDOException';

                    $failures[] = new \Maatwebsite\Excel\Validators\Failure(
                        $this->row_counter,
                        'PDOException',
                        [$e->getMessage()],
                        ['PDOException']
                    );

                    if ($this instanceof SkipsOnFailure) {
                        $this->onFailure(...$failures);
                        // throw new RowSkippedException(...$failures);
                    }
                }
            } catch (RowSkippedException $e) {
                //dd($e->getMessage());
            }

            $this->handled++;
            $this->updateProgress();
        }
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     * @return Importer
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeMap()
    {
        return $this->attribute_map;
    }

    /**
     * @param mixed $map
     * @return Importer
     */
    public function setAttributeMap($map)
    {
        $this->attribute_map = $map;

        return $this;
    }

    /**
     * @param mixed $rules
     * @return Importer
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->model_class;
    }

    /**
     * @param mixed $model_class
     * @return Importer
     */
    public function setModelClass($model_class)
    {
        $this->model_class = $model_class;

        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function getErrorsArray()
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
