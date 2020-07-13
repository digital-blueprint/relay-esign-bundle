# API-ESign-Bundle

This Symfony 4.4 bundle provides API endpoints for

- official electronic signature
- qualified electronic signature
- signature verification

for the API-Gateway.

## Prerequisites

- API Gateway with openAPI/Swagger
- PdfAs 4.1.5 as back end server

## Installation

#### Step 1

Copy this bundle to `./bundles/api-esign-bundle`

#### Step 2

Enable this bundle in `./config/bundles.php` by adding this element to the array returned:

```php
...
    return [
        ...
        BP\API\ESignBundle\ESignBundle::class => ['all' => true],
    ];
}
```

#### Step 3

Add the Entities of this bundle to `./config/packages/api_platform.yaml`:

```yaml
...
 	        paths:
                ...
	            - '%kernel.project_dir%/vendor/dbp/api-esign-bundle/src/Entity'
        exception_to_status:
...
```

#### Step 4

Hide some Entities from exposure by api_platform by adding them to `./src/Swagger/SwaggerDecorator.php`:

```php
...
        $pathsToHide = [
            ...
            "/officially_signed_documents/{id}",
            "/qualified_signing_requests/{id}",
        ];

```

#### Step 5

Add this bundle to `./symfony.lock`:

```json
...
    "dbp/api-esign-bundle": {
        "version": "dev-master"
    },
...
```
