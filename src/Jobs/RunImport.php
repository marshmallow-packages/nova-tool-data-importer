<?php

namespace Marshmallow\NovaDataImporter\Jobs;

use App\Nova\Resource;
use Laravel\Nova\Nova;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Field;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Marshmallow\NovaDataImporter\Events\ImportedErrorEvent;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;
use Marshmallow\NovaDataImporter\Events\ImportedSuccessfullyEvent;
use Marshmallow\NovaDataImporter\Events\DataImportPreviewDoneEvent;

class RunImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($import)
    {
    	$this->import = $import;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', -1);
        
        $this->import
                ->setRunningAsJob()
                ->run();
    }
}
