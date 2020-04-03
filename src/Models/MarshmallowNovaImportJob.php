<?php

namespace Marshmallow\NovaDataImporter\Models;

use App\Nova\Resource;
use Laravel\Nova\Nova;
use Laravel\Nova\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Http\Requests\NovaRequest;

class MarshmallowNovaImportJob extends Model
{
    protected $guarded = [];

    protected $casts = [
    	'sample' => 'array',
    	'resources' => 'array',
    	'fields' => 'array',
    	'total_rows' => 'array',
    	'headings' => 'array',
    ];

    public function progressMessage ()
    {
        return $this->progress . ' of ' . $this->total_rows . ' have been processed';
    }

    public function progress ()
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        if ($this->progress == 0) {
            return 0;
        }

        return ($this->progress / $this->total_rows) * 100;
    }

    public function previewData ()
    {
        return [
            'sample' => $this->sample,
            'resources' => $this->resources,
            'fields' => $this->fields,
            'total_rows' => $this->total_rows,
            'headings' => $this->headings,
        ];

        // $sample = (array) $this->sample;
        // $resources = (array) $this->resources;
        // $fields = (array) $this->fields;
        // $total_rows = (int) $this->total_rows;
        // $headings = (array) $this->headings;

        // $sample = array_map('array_filter', $sample);
        // $headings = array_filter($headings);

        // $resources = collect(Nova::$resources);
        // $resources = $resources->filter(function ($resource) {
        //     $static_vars = (new \ReflectionClass((string) $resource))->getStaticProperties();
        //     if(!isset($static_vars['canImportResource'])) {
        //         return true;
        //     }
        //     return isset($static_vars['canImportResource']) && $static_vars['canImportResource'];
        // });

        // $fields = $resources->map(function ($resource) {
        //     $model = $resource::$model;
        //     return new $resource(new $model);
        // })->mapWithKeys(function ($resource) use ($request) {
        //     $fields = collect($resource->creationFields($request))
        //         ->map(function (Field $field) {
        //             return [
        //                 'name' => $field->name,
        //                 'attribute' => $field->attribute
        //             ];
        //         });
        //     return [$resource->uriKey() => $fields];
        // });

        // $resources = $resources->mapWithKeys(function ($resource) {
        //     return [$resource::uriKey() => $resource::label()];
        // });

        // return [
        //     'sample' => $sample,
        //     'resources' => $resources,
        //     'fields' => $fields,
        //     'total_rows' => $total_rows,
        //     'headings' => $headings,
        // ];
        // return response()->json(compact('sample', 'resources', 'fields', 'total_rows', 'headings'));
    }
}
