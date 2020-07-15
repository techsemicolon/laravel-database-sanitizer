# Sanitize your existing database with dummy data from a single artisan command

Laravel factories and database seeding is really great tool to populate dummy data. However, sometimes all you want is a real production-like data to work with. Because that mostly contains all workflows and different data sets created by different domain events. 

Ideally, it is not a great idea to have real production information in your local development environment as it may contain customer's personal details, patient information etc. This package helps you to `sanitize` your production-like database with dummy values, so you don't have to worry about keeping important production information on your development/testing environments.

## Installation : 

~~~bash
composer require techsemicolon/laravel-database-sanitizer
~~~

## Usage : 

TBA 

## License : 

This psckage is open-sourced software licensed under the MIT license