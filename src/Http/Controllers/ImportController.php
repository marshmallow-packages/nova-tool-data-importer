<?php

namespace Marshmallow\NovaDataImporter\Http\Controllers;

use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\NovaDataImporter\Import;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;

class ImportController
{
    public function preview(NovaRequest $request, $file)
    {
        return (new Import($request, $file))->preview();
    }

    public function import(NovaRequest $request, $file)
    {
        return (new Import($request, $file))->import();
    }

    public function progress(NovaRequest $request, $file)
    {
        $job = MarshmallowNovaImportJob::where('file', $file)->get()->first();

        return response()->json([
            'status' => 'The import is running. We\'re at ' . floor($job->progress()) . '%.',
            'progress_message' => $job->progressMessage(),
            'progress' => $job->progress(),
        ]);
    }
}
