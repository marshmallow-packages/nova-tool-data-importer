<?php

namespace Marshmallow\NovaDataImporter\Http\Controllers;

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
