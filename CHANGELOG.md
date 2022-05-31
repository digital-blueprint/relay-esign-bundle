# ???

* Improved documentation on signature parameters and signature block
  positioning.
* Added health checks that check if the configured pdf-as-web instances are
  reachable and their SOAP interfaces are available.
* Show the duration of the signature operations in the Symfony profiler.

Breaking:

* Setting a "profile" when creating a qualified signature is now mandatory.
* Switch all API paths from "snake_case" to "kebap-case" to match other bundles.

# v0.2.2

* Support for PHP 8.0/8.1
* Fixed some deprecations with Symfony 5.4
