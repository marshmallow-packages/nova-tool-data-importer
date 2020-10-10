![alt text](https://marshmallow.dev/cdn/media/logo-red-237x46.png "marshmallow.")

# Marshmallow Laravel Nova Importer
[![Version](https://img.shields.io/packagist/v/marshmallow/mr-mallow)](https://github.com/marshmallow-packages/mr-mallow)
[![Issues](https://img.shields.io/github/issues/marshmallow-packages/mr-mallow)](https://github.com/marshmallow-packages/mr-mallow)
[![Licence](https://img.shields.io/github/license/marshmallow-packages/mr-mallow)](https://github.com/marshmallow-packages/mr-mallow)
![PHP Syntax Checker](https://github.com/marshmallow-packages/mr-mallow/workflows/PHP%20Syntax%20Checker/badge.svg)

Import data to your Laravel Nova models. You can use jobs if you're expecting large files.

<a href="https://marshmallow.dev/cdn/readme/package-nova-data-importer/marshmallow-small.gif" target="_blank">
    See it in action in a GIF    
</a>

## Installation

You can install the package via composer:
```bash
composer require marshmallow/nova-data-importer
```

The importer needs to have a table to store some data so please run a migration.
```bash
php artisan migrate
```

## Usage
In `app/Providers/NovaServiceProvider.php` you need to add the Import tool.
```
public function tools()
{
    return [
        ...
        new \Marshmallow\NovaDataImporter\NovaDataImporter,
    ];
}
```

## Set up queues (optional)
First, we need to make sure you are able to run queues. If you are already running queues you don't have to change your current behaviour.

Prepare your application to handle queues from the database. Skip this if you already have queues set up.
```bash
php artisan queue:table
php artisan migrate
```

Update your `.env` file first:
```env
QUEUE_CONNECTION=database

BROADCAST_DRIVER=pusher

PUSHER_APP_ID=XXX
PUSHER_APP_KEY=XXX
PUSHER_APP_SECRET=XXX
PUSHER_APP_CLUSTER=eu
```

Last; Inside your `config/app.php` uncomment the line below if it is still commented.
```php
[
   'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
    ],
]
```

Start your worker and you're good to go!
```bash
php artisan queue:work --queue=default
```

## Options
If you want to override some of the default options, you need to publish the config file with `php artisan vendor:publish`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security

If you discover any security related issues, please email stef@marshmallow.dev instead of using the issue tracker.

## Credits

- [All Contributors](../../contributors)
- Package is based on [laravel-nova-csv-import by simonhamp](https://github.com/simonhamp/laravel-nova-csv-import)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
