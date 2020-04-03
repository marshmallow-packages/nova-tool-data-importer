<?php

return [

	/**
	 * The importer that will be used to import the uploaded data to your models.
	 * You can change this if you don't like the default behaviour.
	 */
    'importer' => Marshmallow\NovaDataImporter\Importer::class,


    /**
     * If you are experinsing issues with max execution time or wish to have a faster
     * and cleaner user experience, you can tell the importer to use jobs. Don't forget
     * to start your worker if you are enableing this feature.
     */
    'use_jobs' => false,


    /**
     * If you are using jobs and want Pusher to log information to your console, you can
     * set this to true.
     */
    'pusher_log_to_console' => false,

    /**
     * We make the pusher_key available for Vue to set up the connection
     */
    'pusher_key' => env('PUSHER_APP_KEY'),
];
