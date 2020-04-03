<?php

namespace Marshmallow\NovaDataImporter;

use App\Nova\Resource;
use Laravel\Nova\Nova;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\NovaDataImporter\ImportRun;
use Marshmallow\NovaDataImporter\ImportPreview;
use Marshmallow\NovaDataImporter\Jobs\RunImport;
use Marshmallow\NovaDataImporter\Jobs\PrepareDataImportPreview;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;

class Import
{
	protected $request;
	protected $file;

	public function __construct ($request, $file)
	{
		$this->request = $request;
		$this->file = $file;
	}

	public function import ()
	{
		$resource_name = $this->request->input('resource');
        $this->request->route()->setParameter('resource', $resource_name);
        $resource = Nova::resourceInstanceForKey($resource_name);

        $class = config('nova-data-importer.importer');
        $importer = new $class;

        $importer->setFile($this->file)
            ->setUnique($this->request->unique)
            ->setResource($resource)
            ->setAttributes($resource->creationFields($this->request)->pluck('attribute'))
            ->setAttributeMap($this->request->mappings)
            ->setRules($this->extractValidationRules($this->request, $resource)->toArray())
            ->setModelClass(get_class($resource->resource));


        $import = (new ImportRun)
        				->setImporter($importer)
        				->setFile($this->file);

        if (config('nova-data-importer.use_jobs')) {
        	RunImport::dispatch($import);
        } else {
        	return $import->run();
        }

	}

	public function preview ()
	{
		$request = $this->request;
		/**
		 * Return prepared data if this is already available
		 * in the database.
		 */
		if ($preview = MarshmallowNovaImportJob::where('file', $this->file)->get()->first()) {
            return $preview->previewData($request);
        }

		$class = config('nova-data-importer.importer');
        $importer = new $class;

        $resources = collect(Nova::$resources);
        
        $resources = $resources->filter(function ($resource) {
            $static_vars = (new \ReflectionClass((string) $resource))->getStaticProperties();
            if(!isset($static_vars['canImportResource'])) {
                return true;
            }
            return isset($static_vars['canImportResource']) && $static_vars['canImportResource'];
        });

        $fields = $resources->map(function ($resource) {
            $model = $resource::$model;
            return new $resource(new $model);
        })->mapWithKeys(function ($resource) use ($request) {
            $fields = collect($resource->creationFields($request))
                ->map(function (Field $field) {
                    return [
                        'name' => $field->name,
                        'attribute' => $field->attribute
                    ];
                });
            return [$resource->uriKey() => $fields];
        });

        $resources = $resources->mapWithKeys(function ($resource) {
            return [$resource::uriKey() => $resource::label()];
        });

        $preview = (new ImportPreview)
        				->setImporter($importer)
        				->setFile($this->file)
        				->setResources($resources)
        				->setFields($fields);

        if (config('nova-data-importer.use_jobs')) {
        	PrepareDataImportPreview::dispatch($preview);
        } else {
        	return $preview->generate()->previewData();
        }
	}

	protected function extractValidationRules($request, Resource $resource)
    {
        return collect($resource::rulesForCreation($request))->mapWithKeys(function ($rule, $key) {
            foreach ($rule as $i => $r) {
                if (! is_object($r)) {
                    continue;
                }

                // Make sure relation checks start out with a clean query
                if (is_a($r, Relatable::class)) {
                    $rule[$i] = function () use ($r) {
                        $r->query = $r->query->newQuery();
                        return $r;
                    };
                }
            }

            return [$key => $rule];
        });
    }
}