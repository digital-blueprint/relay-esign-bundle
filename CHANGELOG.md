# [Unreleased]

* Improved documentation on signature parameters and signature block
  positioning.
* Added health checks that check if the configured pdf-as-web instances are
  reachable and their SOAP interfaces are available.
* Show the duration of the signature operations in the Symfony profiler.

Added:

* A new `dbp:relay:esign:sign:qualified` Symfony command for creating a
  qualified signature from the CLI.
* A new `dbp:relay:esign:sign:advanced` Symfony command for creating an
  advanced signature from the CLI.

Deprecated:

* The `callback_url` and `error_callback_url` options are now deprecated. The
  callback pages are now provided by this bundle and exposed via the API server
  if the the options aren't set.

Breaking:

* Setting a "profile" when creating a qualified signature is now mandatory.
* Switch all API paths from "snake_case" to "kebap-case" to match other bundles.

# v0.2.2

* Support for PHP 8.0/8.1
* Fixed some deprecations with Symfony 5.4
