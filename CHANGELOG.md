# Changelog

## Unreleased

* The deprecated `callback_url` and `error_callback_url` options in the bundle config have been removed. The callback
  handling is now handled by the bundle itself. In case pdf-as-web has URL whitelisting enabled make sure that the
  bundle provided endpoints are allowed.

## v0.5.0

* The frontend callback message gained a new `code` field, which replaces the `sessionId` field, which is now deprecated.
  Pass the `code` value to the API endpoints instead of the `sessionId` value.
* (potential API break) The CLI signing commands now take multiple input and output paths. Instead of passing
  `in.pdf out.pdf` you now have to pass `in.pdf -o out.pdf`. You can also pass multiple PDFs at once, for example
  `in.pdf in2.pdf -o out.pdf -o out2.pdf`, to sign multiple documents. They will be matched by the order they are
  passed. Note that the same signing parameters are used for all passed documents.

## v0.4.9

* Add support for zbateson/mail-mime-parser v4

## v0.4.8

* Dependency cleanups

## v0.4.7

* Add support for Symfony 7.4

## v0.4.6

* Add API parameter `invisible` to create invisible signatures via the API.
* Add `dbp:relay:esign:preview` CLI command for generating signature block previews.

## v0.4.5

* Add support for PHP 8.5

## v0.4.4

* Add CLI-only option for creating invisible signatures for all profiles.
* Drop support for api-platform <4.1

## v0.4.3

* Drop support for PHP 8.1
* Drop support for Psalm
* Add support for api-platform 4.1+, drop support for <3.4

## v0.4.2

* Fix a regression where signing profiles without user text configuration would fail.

## v0.4.1

* The CLI commands gained a new `--user-text` option for specifying custom user text to be included in the signature block
* The CLI commands gained a new `--user-image-path` option for passing a path to a PNG file for overriding the signature block image

## v0.4.0

Note: This release only contains some breaking changes for the configuration.

* config: Rework the structure of the `user_text` config:
  * `user_text_table` -> `user_text.target_table`
  * `user_text_row` -> `user_text.target_row`
  * `user_text_attach_parent` -> `user_text.attach.parent_table`
  * `user_text_attach_child` -> `user_text.attach.child_table`
  * `user_text_attach_row` -> `user_text.attach.parent_row`
* config: profile names across both the `advanced` and `qualified` signature
  flows are now required to be unique.
* authz: new authorization config using authz expressions and authz attributes.
  The old Symfony role based system is still supported and works as before. See
  the docs for more information.

## v0.3.18

* Fix running under phpstan v1 for integration tests pruposes

## v0.3.17

* Drop support for api-platform v2
* Drop support for Symfony v5
* Fix some endpoints in the API docs not being hidden if the experimental
  signature verification isn't enabled.
* Support for PHP 8.4
* Update to phpstan v2

## v0.3.16

* Update core

## v0.3.15

* Fix an assertion error when running the unit tests

## v0.3.14

* Port to PHPUnit 10
* Add support for zbateson/mail-mime-parser v3
* Add support for api-platform 3.3
* Port from doctrine annotations to PHP attributes

## v0.3.13

* Add support for api-platform 3.2

## v0.3.12

* Some preparations for api-platform v3
* docs: minor updates for the Python example

## v0.3.11

* Add support for Symfony 6

## v0.3.10

* dev: replace abandoned composer-git-hooks with captainhook.
  Run `vendor/bin/captainhook install -f` to replace the old hooks with the new ones
  on an existing checkout.

## v0.3.9

* Update to psalm v5

## v0.3.8

* Drop support for PHP 7.4/8.0

## v0.3.7

* Drop support for PHP 7.3

## v0.3.6

* Remove embedded json-ld context from the signature responses.

## v0.3.5

* Port to the new api-platform metadata system. No user visible changes.

## v0.3.4

* Update to api-platform v2.7

## v0.3.3

* tests: don't fail if symfony/dotenv is installed

## v0.3.0

* Improved documentation on signature parameters and signature block
  positioning.
* Added health checks that check if the configured pdf-as-web instances are
  reachable and their SOAP interfaces are available.
* Show the duration of the signature operations in the Symfony profiler.
* Support for PHP 8.2

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

## v0.2.2

* Support for PHP 8.0/8.1
* Fixed some deprecations with Symfony 5.4
