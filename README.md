# Laravel date range

Easy handling of Laravel models that have a start / end date, or a set of date ranges.

This package is built for Laravel 8-9.

## Setup

### Install

```bash
composer require "greenimp/laravel-date-range:^2.0.0"
```

### Configuring the package

Publishing the [config file](./config/date-range.php) is optional:

```bash
php artisan vendor:publish --provider="GreenImp\DateRange\DateRangeServiceProvider" --tag="config"
```

### Set up the DB

If you want to use [multiple date ranges](#multiple-date-ranges) on a single model, then you need to create the DB
tables, unless you are using a custom model.

> **Note:** There are several configuration options that will affect the migration file, when run. Please read over them prior to running the migration.

First, publish the migrations:

```bash
php artisan vendor:publish --provider="GreenImp\DateRange\DateRangeServiceProvider" --tag="migrations"
```

The run them:

> **Note** If your config is cached, the migration will fail. You will need to clear the config cache **before** running
> the migration:
> 
> ```bash
> php artisan config:clear
> ```

```bash
php artisan migrate
```

## Usage

### Single date ranges

If your model only requires a single start / end date (e.g. a one off event), the model must implement the
`HasDateRange` interface, and the `InteractsWithDateRange` trait.

The trait contains an abstract method `getDateRangeOptions`, which you must implement yourself.

Your models' migrations should have `datetime` fields to save the start / end dates to.

> **Note:** You can also use the [`DateRange`](./src/Models/DateRange.php) model directly, rather than building your own custom class, if you prefer.

Here's an example of how to implement the trait:

```php
<?php
namespace App\Models;

use GreenImp\DateRange\Contracts\HasDateRange;
use GreenImp\DateRange\InteractsWithDateRange;
use GreenImp\DateRange\Options\DateRangeOptions;
use Illuminate\Database\Eloquent\Model;

class SingleDateModel extends Model implements HasDateRange
{
    use InteractsWithDateRange;
    
    /**
     * Get the options for the date range.
     *
     * @return DateRangeOptions
     */
    public function getDateRangeOptions(): DateRangeOptions
    {
        return DateRangeOptions::create()
            // these are optional - if not called, the default values from the config will be used
            ->startAtField('from')
            ->endAtField('to');
    }
}
```

An example migration for the model:

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up()
    {
        Schema::create('single_date_models', function (Blueprint $table) {
            $table->id();
            $table->datetime('from'); // Field name same as the `startAtField` in your `getDateRangeOptions`
            $table->datetime('to'); // Field name same as the `endAtField` in your `getDateRangeOptions`
            $table->timestamps();
        });
    }
}
```

### Multiple date ranges

If your model has multiple date ranges (e.g. A repeating event such as a national holiday), the model must implement the
`HasDateRanges` interface, and the `InteractsWithDateRanges` trait.

The trait adds a new `dates` relationship, which links to the list of date ranges.

The trait also contains an abstract method `getDateRangeOptions`, which you must implement yourself.

> **Note:** You can also use the [`Event`](./src/Models/Event.php) model directly, rather than building your own custom class, if you prefer.

```php
<?php
namespace App\Models;

use GreenImp\DateRange\Contracts\HasDateRanges;
use GreenImp\DateRange\InteractsWithDateRanges;
use Illuminate\Database\Eloquent\Model;

class MultipleDateModel extends Model implements HasDateRanges
{
    use InteractsWithDateRanges;
    
    /**
     * Get the options for the date ranges.
     *
     * @return DateRangesOptions
     */
    public function getDateRangesOptions(): DateRangesOptions
    {
        return DateRangesOptions::create()
            // these are optional - if not called, the default values from the config will be used
            ->dateRangeModel(\App\Models\SingleDateModel::class)
            ->foreignKeyName('parent')
            ->polymorphic(false);
    }
}
```

> **Note:** Unless you're using a [custom model](#custom-date-range-model) for the date ranges, you'll need to
> [run the migration](#set-up-the-db).

### Customisation

#### Custom Date Range model

By default, the `dates` relationship on the `InteractsWithDateRanges` trait, for multiple date ranges, will use the
[`DateRange`](./src/Models/DateRange.php) model that comes with this package.

If you want to use your own custom class, you can use any model that implements the `HasDateRange` interface, and the
`InteractsWithDateRange` trait.

To set up your custom model, follow the instructions for creating a [single date range model](#single-date-ranges).

You also need to update the config file, to use your custom class:

```php
<?php

return [
    'models' => [
        // Set the value to your custom class.
        'date_range' => \GreenImp\DateRange\Models\DateRange::class,
        ...
    ],
    ...
];
```

#### Database migration options

##### Table name

The migration file that comes with this package will create a table to store the `DateRange` models in, called
`date_ranges`.

You can change the name of the table in the config file:

```php
<?php

return [
    ...
    'table_names' => [
        // Set the value to your custom table name.
        'date_ranges' => 'date_ranges',
    ],
    ...
];
```

##### Table columns

You can also rename the table columns that will be used in the migration.

> **Note:** This will also affect the default field names for the start / end dates of the `DateRangeOptions` class,
> used in the `getDateRangeOptions` method.

#### Polymorphism

By default, this package uses a [polymorphic one to many](https://laravel.com/docs/9.x/eloquent-relationships#one-to-many-polymorphic-relations) relationship between the parent models and the `DateRange` models.

The migration file and model relationships reflect this.

If you wish to use a standard [one to many](https://laravel.com/docs/9.x/eloquent-relationships#one-to-many) relationship, you can set it in the config:

```php
<?php

return [
    ...
    'relationship' => [
        ...
        'polymorphic' => false,
        ...
    ],
    ...
];
```
