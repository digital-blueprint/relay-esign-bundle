# Relay ESign Bundle

[GitHub](https://github.com/digital-blueprint/relay-esign-bundle) |
[Packagist](https://packagist.org/packages/dbp/relay-esign-bundle) |
[Frontend Application](https://github.com/digital-blueprint/esign-app) |
[ESign Website](https://dbp-demo.tugraz.at/site/software/esign.html)

[![Test](https://github.com/digital-blueprint/relay-esign-bundle/actions/workflows/test.yml/badge.svg)](https://github.com/digital-blueprint/relay-esign-bundle/actions/workflows/test.yml)

The electronic signature bundle provides an API for interacting with multiple
[pdf-as](https://www.egiz.gv.at/en/schwerpunkte/16-pdf-as) and [moa-ssp](https://www.egiz.gv.at/en/schwerpunkte/13-moaspssid)
servers and allows signing and signature verification of PDF files using [PAdES](https://en.wikipedia.org/wiki/PAdES).

For more details see the [docs](./docs/index.md) or the [ESign Website](https://dbp-demo.tugraz.at/site/software/esign.html).

There is a corresponding frontend application that uses this API at [ESign Frontend Application](https://github.com/digital-blueprint/esign-app).

## Bundle installation

You can install the bundle directly from [packagist.org](https://packagist.org/packages/dbp/relay-esign-bundle).

```bash
composer require dbp/relay-esign-bundle
```

## Integration into the Relay API Server

* Add the bundle to your `config/bundles.php` in front of `DbpRelayCoreBundle`:

```php
...
Dbp\Relay\EsignBundle\DbpRelayEsignBundle::class => ['all' => true],
Dbp\Relay\CoreBundle\DbpRelayCoreBundle::class => ['all' => true],
];
```

* Run `composer install` to clear caches

## Configuration

For more details see the [Configuration Documentation](./docs/config.md).

If you were using the [DBP API Server Template](https://github.com/digital-blueprint/relay-server-template)
as template for your Symfony application, then an example configuration file should have already been generated for you.

To handle locking you need to [configure locking in the core bundle](https://github.com/digital-blueprint/relay-core-bundle#bundle-config).

You also need to [configure the Symfony Messenger in the core bundle](https://github.com/digital-blueprint/relay-core-bundle#bundle-config) to check out guests after a certain amount of time.

For more info on bundle configuration see <https://symfony.com/doc/current/bundles/configuration.html>.
