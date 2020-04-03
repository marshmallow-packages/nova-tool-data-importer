<?php

namespace Marshmallow\NovaDataImporter\Http\Controllers;

use Laravel\Nova\Http\Requests\NovaRequest;

class ConfigController
{
	/**
	 * Make the config file available for Vue
	 */
    public function index()
    {
        return response()->json(
        	config('nova-data-importer')
        );
    }
}
