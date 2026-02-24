# Bundle Configuration

Created via `./bin/console config:dump-reference DbpRelayEsignBundle | sed '/^$/d'`

```yaml
# Default configuration for "DbpRelayEsignBundle"
dbp_relay_esign:
  qualified_signature:
    # The URL to the PDF-AS server for qualified signatures
    server_url:           ~ # Required, Example: 'https://pdfas.example.com/pdf-as-web'
    # The URL pdf-as will redirect to when the signature is done (optional)
    callback_url:         ~ # Deprecated (Since dbp/relay-esign-bundle ???: The "callback_url" option is deprecated. The API server now provides the callback URL itself.), Example: 'https://pdfas.example.com/static/callback.html'
    # The URL pdf-as will redirect to when the signature failed (optional)
    error_callback_url:   ~ # Deprecated (Since dbp/relay-esign-bundle ???: The "error_callback_url" option is deprecated. The API server now provides the callback URL itself.), Example: 'https://pdfas.example.com/static/error.html'
    profiles:
      # Prototype
      -
        # The name of the profile, this needs to be passed to the API
        name:                 ~ # Required, Example: myprofile
        # The Symfony role required to use this profile. If set, overrides the authorization config.
        role:                 ~ # Deprecated (Since dbp/relay-esign-bundle ???: The "role" option is deprecated. Use the global authorization node instead.), Example: ROLE_FOOBAR
        # The PDF-AS signature profile ID to use
        profile_id:           ~ # Required, Example: MYPROFILE
        # For extending the PDF-AS signature layout with user provided text (optional)
        user_text:
          # The profile table ID to attach the content to.
          target_table:         ~ # Required, Example: usercontent
          # The index of the first unset row in the table (starts with 1)
          target_row:           ~ # Required, Example: '1'
          # In case there is content "child_table" will be attached to "parent_table" at "parent_row" (optional)
          attach:
            # The name of the parent table
            parent_table:         ~ # Required, Example: parent
            # Child table name
            child_table:          ~ # Required, Example: child
            # The index of the row where the child table will be attached to
            parent_row:           ~ # Required, Example: '4'
  advanced_signature:
    # The URL to the PDF-AS server for advanced signatures
    server_url:           ~ # Required, Example: 'https://pdfas.example.com/pdf-as-web'
    profiles:
      # Prototype
      -
        # The name of the profile, this needs to be passed to the API
        name:                 ~ # Required, Example: myprofile
        # The Symfony role required to use this profile. If set, overrides the authorization config.
        role:                 ~ # Deprecated (Since dbp/relay-esign-bundle ???: The "role" option is deprecated. Use the global authorization node instead.), Example: ROLE_FOOBAR
        # The PDF-AS signature key ID used for singing
        key_id:               ~ # Required, Example: MYKEY
        # The PDF-AS signature profile ID to use
        profile_id:           ~ # Required, Example: MYPROFILE
        # For extending the PDF-AS signature layout with user provided text (optional)
        user_text:
          # The profile table ID to attach the content to.
          target_table:         ~ # Required, Example: usercontent
          # The index of the first unset row in the table (starts with 1)
          target_row:           ~ # Required, Example: '1'
          # In case there is content "child_table" will be attached to "parent_table" at "parent_row" (optional)
          attach:
            # The name of the parent table
            parent_table:         ~ # Required, Example: parent
            # Child table name
            child_table:          ~ # Required, Example: child
            # The index of the row where the child table will be attached to
            parent_row:           ~ # Required, Example: '4'
  authorization:
    policies:             []
    roles:
      # Returns true if the user is allowed to sign things in general.
      ROLE_SIGNER:          user.isAuthenticated()
      # Returns true if the user is allowed to verify signatures.
      ROLE_VERIFIER:        'false'
    resource_permissions:
      # Returns true if the user can sign with the given profile.
      ROLE_PROFILE_SIGNER:  'false'
    attributes:           []
```

## Examples

```yaml
dbp_relay_esign:
  qualified_signature:
    server_url: 'https://sig.myuni.at/pdf-as-web'
    profiles:
      - name: default
        profile_id: 'SIGNATURBLOCK_MYUNI_QUAL'
  authorization:
    roles:
      ROLE_SIGNER: 'user.isAuthenticated()'
    resource_permissions:
      ROLE_PROFILE_SIGNER: 'true'
```

```yaml
dbp_relay_esign:
  advanced_signature:
    server_url: 'https://pdfas.myuni.at/pdf-as-web'
    profiles:
      - name: official
        key_id: myuni-official
        profile_id: SIGNATURBLOCK_MYUNI_AMTSSIGNATUR
      - name: sap
        key_id: myuni-sap
        profile_id: SIGNATURBLOCK_MYUNI_SAP
  authorization:
    roles:
      ROLE_SIGNER: 'user.isAuthenticated()'
    resource_permissions:
      ROLE_PROFILE_SIGNER: >
        (resource.getName() === "official" && user.get("SCOPE_OFFICIAL-SIGNATURE")) ||
        (resource.getName() === "sap" && user.get("SCOPE_SAP-SIGNATURE"))
```

## Authorization

The bundle authorization config consists of two roles:

* `ROLE_SIGNER` - Should evaluate to true if the user is allowed to sign in general.
* `ROLE_VERIFIER` - Should evaluate to true if the user is allowed to verify signatures.

In addition, there is one resource based role:

* `ROLE_PROFILE_SIGNER` - Returns true if the user can sign with the given profile.
  The resource is a profile object which only has a `getName()` method returning the profile name.

For a user to be able to sign with a specific profile both the `ROLE_SIGNER` and
`ROLE_PROFILE_SIGNER` roles need to evaluate to true for the selected profile.

## Symfony-Role based authorization (deprecated)

Each profile in the bundle configuration can have a `role` key which is a
Symfony role that is required to use this profile. If set, this overrides the
global authorization configuration for this profile alone.

To port to the new authorization system, you need to remove the `role` key from
the config, which makes the global authorization configuration apply to the
profile. Instead of checking for a Symfony role you need to check for a user
attribute.

before:

```yaml
profiles:
  name: my-profile
  profile_id: 'SIGNATURBLOCK_SMALL_DE_NOTE_PDFA'
  role: ROLE_FOOBAR
```

after:

```yaml
profiles:
  name: my-profile
  profile_id: 'SIGNATURBLOCK_SMALL_DE_NOTE_PDFA'
  authorization:
    resource_permissions:
      ROLE_PROFILE_SIGNER: >
        resource.getName() === "my-profile" && user.get("SOMETHING_THAT_IS_TRUE_IF_WE_CAN_SIGN_USING_MY_PROFILE")
```

## User Defined Text

This allows the user of the API to append custom rows to a table in the visible
signature block.

![](user_text.png){: style="max-width:400px; width: 100%" }

Both `qualified_profile` as well as every entry in `advanced_profiles` allows
setting an optional user text configuration:

* `user_text.target_table` - The profile table ID to attach the content to
* `user_text.target_row` - The index of the first unset row in the table (starts with 1)
* `user_text.attach.parent_table`/`user_text.attach.child_table`/`user_text.attach.parent_row` (optional) - In case there is content to add the `child` will be attached to `parent` at row `row`.

The extra attachment configuration is required because pdf-as-web doesn't allow
reachable empty tables, so we either have to add to existing tables or attach a
new table depending on wether we want to add rows or not. Example:

```yaml
# We attach user text rows to "user" at row 1
# and if there are any rows we attach "user" to "info" at row 2
user_text:
  target_table: user
  target_row: 1
  attach:
    parent_table: info
    child_table: user
    parent_row: 2
```

To allow the esign bundle to add custom text to the signature profiles you need
to whitelist these configuration objects in the `pdf-as-web.properties` file of
`pdf-as-web`:

```ini
allow.ext.overwrite=true
ext.overwrite.wl.01=^sig_obj\\.SIGNATURBLOCK_MYUNI_AMTSSIGNATUR\\..*$
ext.overwrite.wl.02=^sig_obj\\.SIGNATURBLOCK_MYUNI_SAP\\..*$
```

Note that this allows changing everything regarding the signature profile, so
make sure the SOAP API isn't publicly reachable.

## PDF-AS Configuration

It's recommended to have two separate pdf-as servers for the advanced and
qualified signatures because the advanced signature one can not be public while
the qualified one needs to be accessible from the internet for talking with
handy-signatur.at

### Qualified Server

In `pdf-as-web.properties` signing via SOAP and mobile signing needs to be enabled:

```ini
soap.sign.enabled=true
mobile.sign.enabled=true
```

If URL whitelisting is enabled (it's not by default, but recommended) then the esign bundle callback endpoints need to
be explicitly allowed:

```ini
whitelist.enabled=true
whitelist.url.01=^https://my-api\\.my-uni\\.at.*$
```

In addition, in the pdf-as configuration (`cfg/config.properties`) the referenced profiles need to be defined.
Usually they are defined in extra files under `cfg/profiles` and then included in `cfg/config.properties`:

```ini
include.01 = profiles/SIGNATURBLOCK_MYUNI_AMTSSIGNATUR.properties
include.02 = profiles/SIGNATURBLOCK_MYUNI_SAP.properties
```

The signature property files included need to use the profile key referenced in the bundle config:

```ini
...
sig_obj.SIGNATURBLOCK_MYUNI_AMTSSIGNATUR = ...
...
```

### Advanced Server

The pdf-as-web service needs to be configured in `pdf-as-web.properties` to contain a keystore list
containing the keys referenced in the bundle config file.

```ini
# Local key store list
ksl.myuni-official.enabled=true
ksl.myuni-official.file=/path/to/tomcat/conf/pdf-as/my-pdf-cert.p12
ksl.myuni-official.type=PKCS12
ksl.myuni-official.pass=keystore-password
ksl.myuni-official.key.alias=mycert
ksl.myuni-official.key.pass=cert-password
```

Signing via SOAP needs to be enabled:

```ini
soap.sign.enabled=true
```

In addition, in the pdf-as configuration (`cfg/config.properties`) the referenced profiles need to be defined.
Usually they are defined in extra files under `cfg/profiles` and then included in `cfg/config.properties`:

```ini
include.01 = profiles/SIGNATURBLOCK_MYUNI_AMTSSIGNATUR.properties
include.02 = profiles/SIGNATURBLOCK_MYUNI_SAP.properties
```

The signature property files included need to use the profile key referenced in the bundle config:

```ini
...
sig_obj.SIGNATURBLOCK_MYUNI_AMTSSIGNATUR = ...
...
```