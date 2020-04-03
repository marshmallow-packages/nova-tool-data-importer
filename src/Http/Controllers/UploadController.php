<?php

namespace Marshmallow\NovaDataImporter\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Marshmallow\NovaDataImporter\Importer;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;

class UploadController
{
    use Importable;

    public function handle(NovaRequest $request)
    {
        /**
         * Validate the uploaded file
         */
        $data = Validator::make($request->all(), [
            'file' => 'required|file',
        ])->validate();

        $file = $request->file('file');

        // Store the file temporarily
        $hash = File::hash($file->getRealPath());
        $file->move(storage_path('nova/laravel-nova-import-csv/tmp'), $hash);

        return response()->json(['result' => 'success', 'file' => $hash]);
    }

    public function delete ($file)
    {
        /**
         * Remove the tmp uploaded file
         */
        if (file_exists(storage_path('nova/laravel-nova-import-csv/tmp/' . $file))) {
            unlink(storage_path('nova/laravel-nova-import-csv/tmp/' . $file));
        }

        /**
         * Remove the job from the database
         */
        $job = MarshmallowNovaImportJob::where('file', $file)->get()->first();
        if ($job) {
            $job->delete();
        }
    }
}
