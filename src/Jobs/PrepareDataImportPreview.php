<?php

namespace Marshmallow\NovaDataImporter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Marshmallow\NovaDataImporter\Events\DataImportPreviewDoneEvent;

class PrepareDataImportPreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $preview;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($preview)
    {
        $this->preview = $preview;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', -1);
        $import_job = $this->preview->generate();
        event(new DataImportPreviewDoneEvent($import_job));
    }
}
