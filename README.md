<p align="center">
    <img src="https://cdn.marshmallow-office.com/media/images/logo/marshmallow.transparent.red.png">
</p>
<p align="center">
    <a href="https://github.com/Marshmallow-Development">
        <img src="https://img.shields.io/github/issues/Marshmallow-Development/package-nova-data-importer.svg" alt="Issues">
    </a>
    <a href="https://github.com/Marshmallow-Development">
        <img src="https://img.shields.io/github/forks/Marshmallow-Development/package-nova-data-importer.svg" alt="Forks">
    </a>
    <a href="https://github.com/Marshmallow-Development">
        <img src="https://img.shields.io/github/stars/Marshmallow-Development/package-nova-data-importer.svg" alt="Stars">
    </a>
    <a href="https://github.com/Marshmallow-Development">
        <img src="https://img.shields.io/github/license/Marshmallow-Development/package-nova-data-importer.svg" alt="License">
    </a>
</p>

# Marshmallow Laravel Nova Importer
Import data to your Laravel Nova models. You can use jobs if you're expecting large files.

<a href="https://marshmallow.dev/cdn/readme/package-nova-data-importer/marshmallow-small.gif" target="_blank">
    See it in action in a GIF    
</a>

### Installatie
```
composer require marshmallow/nova-data-importer
```
The importer needs to have a table to store some data so please run a migration.
```
php artisan migrate
```

## Set up queues (optional)
First, we need to make sure you are able to run queues. If you are already running queues you don't have to change your current behaviour.

Update your `.env` file first:
```
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=959587
PUSHER_APP_KEY=e9126a8f20d4d3d565be
PUSHER_APP_SECRET=51af003872680ce889b4
PUSHER_APP_CLUSTER=eu
```

Next run these artisan commands:
```
php artisan queue:table
php artisan migrate
```

Update your `.env` file to let Laravel know you are running your queues from the database.
```
QUEUE_CONNECTION=database
```

Last; Inside your `config/app.php` uncomment the line below if it is still commented.
```
App\Providers\BroadcastServiceProvider::class,
```

Start your worker and you're good to go!
```
php artisan queue:work --queue=default
```

## Add the tool to Nova
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

## Options
If you want to override some of the default options, you need to publish the config file with `php artisan vendor:publish`.

- - -

Copyright (c) 2020 marshmallow
