# Laravel Pulse - Schedule

Laravel Pulse card that list all scheduled tasks.

![Screenshot of a Laravel Pulse card showing all scheduled tasks.](assets/screenshot.png)

## Installation

You can install the package via composer:

```sh
composer require hosmelq/pulse-schedule
```

## Usage

To add the card to the Pulse dashboard, you must first [publish](https://laravel.com/docs/10.x/pulse#dashboard-customization) the dashboard view.

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

The MIT License (MIT). Please see [License File](LICENSE) for more information.
