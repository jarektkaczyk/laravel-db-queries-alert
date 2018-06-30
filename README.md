# Sofa/DbQueriesAlert

#### aka Check Your Queries Bro!

[![Downloads](https://poser.pugx.org/sofa/laravel-db-queries-alert/downloads)](https://packagist.org/packages/sofa/laravel-db-queries-alert) [![stable](https://poser.pugx.org/sofa/laravel-db-queries-alert/v/stable.svg)](https://packagist.org/packages/sofa/laravel-db-queries-alert)

Laravel & Eloquent are beautiful and easy to use. Many packages make things even easier, which is brilliant!
There's price to that though. Often you may find yourself in a situation when your app slows down or freezes completely without any significant change in the code.
One of the reasons to that might be **database querying abuse** by hitting `N+1` query problem on Eloquent relations, or some magic setup in a package you're using somewhere.

---

This package will help you with this problem by checking queries being run and logging `info|warning|error` when specified thresholds are exceeded.

**Aim of the package is simply alerting you, without getting into details**. It provides you some hints on where to look at, and either you spot the problem yourself right away, or you would resort to more in-depth analysis with [laravel-debugbar](https://github.com/barryvdh/laravel-debugbar), [blackfire](https://blackfire.io), [xdebug](https://xdebug.org) or any other tool of your choice.

## Usage
After you [installed](#installation) the package you can customize the thresholds publish configuration by calling:

```
$ php artisan vendor:publish --provider="Sofa\DbQueriesAlert\ServiceProvider"
```

and edit it in `config/db_queries_alert.php`:
```php
return [
    'error' => 100,
    'warning' => 50,
    'info' => 20,
];
```


Now you're good to go. The package will call `Log::error` (or `warning|info`) whenever your app hits given threshold. Catch this error in the monitoring service you're using for the application (or simply check your local `storage/logs/laravel[-YYYY-MM-DD].log` file).



#### Note
This package depends fully on Laravel and is supposed to be used only there, thus there's no DI, it shamelessly uses facades and helpers and does not even try to hide it  ¯\\_(ツ)_/¯

## Installation

1. Add package to your project:
    ```
    path/to/your/app$ composer require sofa/laravel-db-queries-alert
    ```

2. Then add provider manually to your `config/app.php`:

    *Package supports **auto-discovery** so this step is required only for Laravel 5.4.*

    ```php
    // config/app.php

        'providers' => [
            ...
            Sofa\DbQueriesAlert\ServiceProvider::class,
        ],
    ```

3. Add entry to the global middleware stack in `app/Http/Kernel.php` file and flag to your `.env` file:
    ```php
    // app/Http/Kernel.php
    class Kernel extends HttpKernel
    {
        /**
         * The application's global HTTP middleware stack.
         *
         * These are run for every single Request.
         *
         * @var array
         */
        protected $middleware = [
            ...
            \Sofa\DbQueriesAlert\Middleware::class,
        ];



    // .env
    DB_QUERIES_ALERT_ENABLED=true

    ```



#### Contribution

All contributions are welcome, PRs must be **PSR-2 compliant**.
