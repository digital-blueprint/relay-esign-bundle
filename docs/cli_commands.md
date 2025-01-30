# CLI Commands

The bundle provides two Symfony commands for signing PDFs via the command line.
The can for example be used to test the configured profiles.

* `USER-TEXT` is a JSON in the format of `[{"description": "Name", "value": "Max"}, {"description": "Place", "value": "Cyberspace"}]`
* `USER-IMAGE-PATH` is the path to a PNG file which will be used to replace the default signature image

## dbp:relay:esign:sign:advanced

This command will directly sign a PDF.

```console
$ ./bin/console dbp:relay:esign:sign:advanced --help
Description:
  Sign a PDF file

Usage:
  dbp:relay:esign:sign:advanced <profile-id> <input-path> <output-path>

Arguments:
  profile-id            Signing profile ID
  input-path            Input PDF file path
  output-path           Output PDF file path

Options:
      --user-image-path=USER-IMAGE-PATH  Signature image path (PNG)
      --user-text=USER-TEXT              User text JSON
```

## dbp:relay:esign:sign:qualified

This command will ask you to open a website in a browser, authenticate, and then
copy a session ID back to continue the process.

```console
$ ./bin/console dbp:relay:esign:sign:qualified --help
Description:
  Sign a PDF file

Usage:
  dbp:relay:esign:sign:qualified <profile-id> <input-path> <output-path>

Arguments:
  profile-id            Signing profile ID
  input-path            Input PDF file path
  output-path           Output PDF file path

Options:
      --user-image-path=USER-IMAGE-PATH  Signature image path (PNG)
      --user-text=USER-TEXT              User text JSON
```
