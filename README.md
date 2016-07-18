![Alt text](https://tng.zerolime.se/_upload/tng/img/bed7d297-04c5-4568-afae-cc4bec68e3ad_300x120.jpg "urb-it")
----
# urbit-client-php
----

PHP client library for urb-it integration.

## Installing

The recommended way to install the urb-it PHP client is through
[Composer](http://getcomposer.org):
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest version of the urb-it PHP client:
```bash
php composer.phar require urbit/client:dev-master
```

After installing, you need to require Composer's auto-loader:
```php
require 'vendor/autoload.php';
```

You can later update the PHP client using composer:
 ```bash
composer.phar update
 ```

## Legacy Version

For the legacy version of the PHP client, please see the [legacy branch](https://github.com/urbitassociates/urbit-client-php/tree/legacy) or [release v0.1](https://github.com/urbitassociates/urbit-client-php/releases/tag/v0.1).

## Usage

```php
$client = new Urbit\Client('<store_key>', '<shared_secret>');

$openingHours = $client->getOpeningHours('2016-05-21', '2016-05-28');

$validPostalCode = $client->validatePostalCode('113 30');

$order = [...];
$createOrderResponse = $client->createOrder($order);
$validateDeliveryResponse = $client->validateDelivery($order);
```

Only UWA:

```php
$uwa = new Urbit\UWA();
```

## Documentation

* General API documentation: http://developer.urb-it.com
* Detailed urbit-client-php documentation: http://developer.urb-it.com/docs/php

## Maintainers & Contributors

* Sebastian Mandrean <sebastian@urb-it.com> (Maintainer)
* Ivar Johansson <ivar@urb-it.com> (Contributor)
