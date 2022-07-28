# Qualified Signature Flow

## Browser Interaction
```mermaid
sequenceDiagram
    participant Browser
    participant API as API-Gateway
    participant PdfAs as PDF-AS
    participant ATrust as A-Trust
    autonumber
    Browser->>API: Send PDF to sign
    loop Sanity Check
        API->>API: Check if uploaded file is valid
    end
    API->>PdfAs: Send PDF to sign
    PdfAs->>ATrust: Demand signing process
    ATrust-->>PdfAs: Deliver signing request parameters
    PdfAs-->>API: Deliver signing request redirect URL
    API-->>Browser: Deliver redirect URL with `reqId`
    Browser->>PdfAs: Get form in iFrame
    PdfAs-->>Browser: Send form with automatic execution
    Browser->>ATrust: Redirect to A-Trust
    ATrust-->>Browser: Send Form for credentials in iFrame
    Browser->>ATrust: Send crendentials from iFrame
    ATrust-->>Browser: Send Form for 2FA in iFrame
    Browser->>ATrust: Send 2FA, Waiting for result
    Note over PdfAs, ATrust: Security Layer Communication<br> Create Signature
    ATrust-->>Browser: Deliver redirect URL
    Browser->>PdfAs: Redirect to PDF-AS in iFrame
    PdfAs-->>Browser: Deliver URL with to static callback
    Browser->>API: Redirect to static callback <br> invokeURL in iFrame
    API-->>Browser: Send `sessionId` to frame <br> parent in browser to download file
    Browser->>API: Send `sessionId`
    API->>PdfAs: Send `sessionId`
    PdfAs-->>API: Send signed PDF
    API-->>Browser: Deliver signed PDF
```

## Security Layer Communication
```mermaid
sequenceDiagram
    participant PdfAs as PDF-AS
    participant ATrust as A-Trust
    autonumber
    PdfAs->>ATrust: Request certificate data
    ATrust-->>PdfAs: Send certificate data
    PdfAs->>ATrust: Request for signature from mobileBKU
    ATrust-->>PdfAs: Send signature response
    loop Signature Check
        PdfAs->>PdfAs: Check if signatures are valid
    end
    PdfAs->>ATrust: Send redirect URL for Browser with `sessionId`
```

## Browser Javascript API

The response of calling `/esign/qualified-signing-requests` contains a `url`
property which, in the context of a browser can be loaded in a new windows or an
iframe and will redirect the user to the handy-signatur.at authentication page.
After the authentication is finished the windows will forward a result to the
window creator via the
[postMessage()](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage)
API.

In case the authentication succeeded, the response is:

```js
{
    type: 'pdf-as-callback',
    sessionId: '<sessionId>',
}
```

The `sessionId` can be used to retrieve the signed document via the
`/esign/qualifiedly-signed-documents` endpoint.

In case of an error, the response is:

```js
{
    type: 'pdf-as-error',
    error: '<A readable description of the error>',
    cause: '<Cause of the error, usually empty>',
}
```

The `error` property can be shown to the user.
