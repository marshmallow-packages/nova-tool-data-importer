<?php

namespace Marshmallow\NovaDataImporter\Traits;

use Illuminate\Support\Facades\File;

trait ImportTrait
{
	protected function getFilePath($file)
    {
        return storage_path("nova/laravel-nova-import-csv/tmp/{$file}");
    }

    public function fileType ()
    {
    	$mime = File::mimeType($this->getFilePath($this->file));
    	switch ($mime) {
    		case 'application/vnd.ms-excel':
    			return 'Xls';
    			break;
    		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
    			return 'Xlsx';
    			break;
    		case 'text/csv':
    		case 'text/plain':
    			return 'Csv';
    			break;
    	}

    	return null;
    }
}