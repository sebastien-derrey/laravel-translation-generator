# Laravel translation files generator

Very simple command which generate missing keys in translation files.
Parse blade, and laravel files. 
Compatible with laravel 6.x and 7.x.

## Installation

```
composer require --dev sdlab/laravel-translation-files-generator
```

## Usage

```
php artisan translation:generate-files --locale=fr --locale=en --origin=fr
```

The command will parse all the translation function calls from blade and php files, and add the missing one in the specified files transations files.
* locale: each locale specified will create associated translation file
* origin: will fill the value also for the specified language. Useful when the expressions used in code are already translated in one language

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
