# Relay ESign Bundle

[GitHub](https://github.com/digital-blueprint/relay-esign-bundle) |
[Packagist](https://packagist.org/packages/dbp/relay-esign-bundle) |
[Frontend Application](https://github.com/digital-blueprint/esign-app) |
[ESign Website](https://handbook.digital-blueprint.org/blueprints/esign)

[![Test](https://github.com/digital-blueprint/relay-esign-bundle/actions/workflows/test.yml/badge.svg)](https://github.com/digital-blueprint/relay-esign-bundle/actions/workflows/test.yml)

The electronic signature bundle provides an API for interacting with multiple
[pdf-as](https://www.egiz.gv.at/en/schwerpunkte/16-pdf-as) and [moa-ssp](https://www.egiz.gv.at/en/schwerpunkte/13-moaspssid)
servers and allows signing and signature verification of PDF files using [PAdES](https://en.wikipedia.org/wiki/PAdES).

For more details see the [docs](./docs/README.md) or the [ESign Website](https://handbook.digital-blueprint.org/blueprints/esign).

There is a corresponding frontend application that uses this API at [ESign Frontend Application](https://github.com/digital-blueprint/esign-app).

## Bundle installation

You can install the bundle directly from [packagist.org](https://packagist.org/packages/dbp/relay-esign-bundle).

```bash
composer require dbp/relay-esign-bundle
```

## Configuration

For more details see the [Configuration Documentation](./docs/config.md).

If you were using the [DBP API Server Template](https://github.com/digital-blueprint/relay-server-template)
as template for your Symfony application, then an example configuration file should have already been generated for you.
