# API-ESign-Bundle

This Symfony 4.4 bundle provides API endpoints for

- advanced electronic signature
- qualified electronic signature
- signature verification

for the API-Gateway.

## Prerequisites

- API Gateway with openAPI/Swagger
- PdfAs 4.1.5 as back end server

## Installation

### Step 1

Copy this bundle to `./bundles/api-esign-bundle`

### Step 2

Enable this bundle in `./config/bundles.php` by adding this element to the array returned:

```php
...
    return [
        ...
        Dbp\Relay\EsignBundle\ESignBundle::class => ['all' => true],
    ];
}
```

### Step 3

Add this bundle to `./symfony.lock`:

```json
...
    "dbp/api-esign-bundle": {
        "version": "dev-master"
    },
...
```
