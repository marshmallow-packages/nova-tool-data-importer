<?php

namespace Marshmallow\NovaDataImporter;

use Marshmallow\NovaDataImporter\Traits\ImportTrait;
use Marshmallow\NovaDataImporter\Events\ImportedErrorEvent;
use Marshmallow\NovaDataImporter\Events\ImportedSuccessfullyEvent;

class ImportRun
{
	use ImportTrait;

	protected $file;

	protected $importer;

	protected $is_csv_file = false;

	protected $running_as_job = false;

	public function isCsv ()
	{
		$this->is_csv_file = true;
		return $this;
	}

	public function setFile ($file)
	{
		$this->file = $file;
		return $this;
	}

	public function setImporter ($importer)
	{
		$this->importer = $importer;
		return $this;
	}

	public function setRunningAsJob ()
	{
		$this->running_as_job = true;
		return $this;
	}

	public function run ()
	{
		if ($file_type = $this->fileType()) {
			$this->importer
					->prepare()
					->import($this->getFilePath($this->file), null, $file_type);

	        if (!$this->importer->failures()->isEmpty() || ! $this->importer->errors()->isEmpty()) {
	            $errors = (array) $this->importer->getErrorsArray();
	            $response = [
	    			'result' => 'failure', 
	    			'errors' => $errors, 
	    			'failures' => $this->importer->failures()
	    		];
	    		if ($this->running_as_job) {
	        		event(new ImportedErrorEvent($response));
	        	}
	        	return $response;

	        } else {
	        	$response = [
	                'result' => 'success'
	            ];
	        	if ($this->running_as_job) {
	        		event(new ImportedSuccessfullyEvent($response));
	        	}
	            return $response;
	        }	
		}

		throw new \Exception("Unknown file type");
	}
}