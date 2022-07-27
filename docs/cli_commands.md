# CLI Commands

The bundle provides two Symfony commands for signing PDFs via the command line.
The can for example be used to test the configured profiles.

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
```
