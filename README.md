# Laravel custom mail handler example project

## Installation

```
composer install
php artisan key:generate
```

Create a mock API to send emails to. See [How to create a Postman Mock 
API](https://learning.postman.com/docs/designing-and-developing-your-api/mocking-data/setting-up-mock/).

## Configuration

Copy the example `.env.example`:

```
cp .env.example
```

Set the `CUSTOM_MAIL_URL` value:

```
CUSTOM_MAIL_URL="<your mock API URL>"
```

## Usage

Start server with:

```
php artisan serve
```

Visit [http://127.0.0.1:8000/email](http://127.0.0.1:8000/email) to send an 
email.

## Tests

Run:

```
./vendor/bin/phpunit
```
