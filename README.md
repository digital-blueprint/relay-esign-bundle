# Relay ESign Bundle

This bundle provides API endpoints for

- Advanced electronic signature
- Qualified electronic signature
- (WIP) Signature verification

## Prerequisites

- Relay API Gateway
- PDF-AS 4.1.5+ as a backend server

## Bundle Configuration

Created via `./bin/console config:dump-reference DbpRelayEsignBundle | sed '/^$/d'`

```yaml
# Default configuration for "DbpRelayEsignBundle"
dbp_relay_esign:
    qualified_signature:
        # The URL to the PDF-AS server for qualified signatures
        server_url:           ~ # Example: 'https://pdfas.example.com/pdf-as-web'
        # The URL pdf-as will redirect to when the signature is done
        callback_url:         ~ # Example: 'https://pdfas.example.com/static/callback.html'
        # The URL pdf-as will redirect to when the signature failed
        error_callback_url:   ~ # Example: 'https://pdfas.example.com/static/error.html'
        profiles:
            # Prototype
            -
                # The name of the profile, this needs to be passed to the API
                name:                 ~ # Example: myprofile
                # The Symfony role required to use this profile
                role:                 ~ # Example: ROLE_FOOBAR
                # The PDF-AS signature profile ID to use
                profile_id:           ~ # Example: MYPROFILE
                # The profile table ID to attach the content to. Leave empty to disable user text.
                user_text_table:      ~ # Example: usercontent
                # The index of the first unset row in the table (starts with 1)
                user_text_row:        ~ # Example: 1
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_parent: ~ # Example: parent
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_child: ~ # Example: child
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_row: ~ # Example: 4
    advanced_signature:
        # The URL to the PDF-AS server for advanced signatures
        server_url:           ~ # Example: 'https://pdfas.example.com/pdf-as-web'
        profiles:
            # Prototype
            -
                # The name of the profile, this needs to be passed to the API
                name:                 ~ # Example: myprofile
                # The Symfony role required to use this profile
                role:                 ~ # Example: ROLE_FOOBAR
                # The PDF-AS signature key ID used for singing
                key_id:               ~ # Example: MYKEY
                # The PDF-AS signature profile ID to use
                profile_id:           ~ # Example: MYPROFILE
                # The profile table ID to attach the content to. Leave empty to disable user text.
                user_text_table:      ~ # Example: usercontent
                # The index of the first unset row in the table (starts with 1)
                user_text_row:        ~ # Example: 1
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_parent: ~ # Example: parent
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_child: ~ # Example: child
                # In case there is content "child" will be attached to "parent" at "row" (optional)
                user_text_attach_row: ~ # Example: 4
```