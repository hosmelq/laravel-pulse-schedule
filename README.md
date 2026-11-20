# Laravel Pulse - Schedule

Show your Laravel app's scheduled tasks in a Laravel Pulse card.

![Screenshot of a Laravel Pulse card showing all scheduled tasks.](assets/screenshot.png)

## Requirements

- Laravel 13+
- PHP 8.4+

## Installation

You can install the package via composer:

```sh
composer require hosmelq/laravel-pulse-schedule
```

## Usage

To add the card to the Pulse dashboard, you must first [publish](https://laravel.com/docs/13.x/pulse#dashboard-customization) the dashboard view.

```sh
php artisan vendor:publish --tag=pulse-dashboard
```

You can now add the card.

```html
<x-pulse>
    ...

    <livewire:pulse.schedule cols="8" />

    ...
</x-pulse>

```


## Credits

- [Hosmel Quintana](https://github.com/hosmelq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
