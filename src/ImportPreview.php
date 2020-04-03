<?php

namespace Marshmallow\NovaDataImporter;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Marshmallow\NovaDataImporter\Traits\ImportTrait;
use Marshmallow\NovaDataImporter\Models\MarshmallowNovaImportJob;

class ImportPreview
{
	use ImportTrait;

	protected $file;

	protected $fields;

	protected $importer;

	protected $resources;

	protected $is_csv_file = false;

	public function isCsv ()
	{
		$this->is_csv_file = true;
		return $this;
	}

	public function setImporter ($importer)
	{
		$this->importer = $importer;
		return $this;
	}

	public function setFile ($file)
	{
		$this->file = $file;
		return $this;
	}

	public function setResources ($resources)
	{
		$this->resources = $resources;
		return $this;
	}

	public function setFields ($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	public function generate ()
	{
		if ($file_type = $this->fileType()) {
			$import = $this->importer
	                    ->toCollection($this->getFilePath($this->file), null, $file_type)
	                    ->first();

	        return MarshmallowNovaImportJob::create([
		        'file' => $this->file,
		        'sample' => $import->take(10)->all(),
		        'resources' => $this->resources,
		        'fields' => $this->fields,
		        'total_rows' => $import->count(),
		        'headings' => $import->first()->keys(),
		    ]);
		}

		throw new \Exception("Unknown file type");
	}
}