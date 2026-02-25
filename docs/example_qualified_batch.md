# Qualified Batch Signature Example

The following Python example shows how to attach a qualified signature
to multiple PDF files.

1. Fetch an access token from Keycloak
2. Upload the PDF
3. User verifies their identity for each document + copies the resulting code
4. Extract the signed PDF from the response

```python
import os
from urllib.request import urlopen

import requests

KEYCLOAK_URL = "https://auth-demo.tugraz.at/auth"
API_URL = "https://api-demo.tugraz.at"
PDF_IN_PATH = 'example.pdf'
PDF2_IN_PATH = 'example2.pdf'
ESIGN_PROFILE = "default"

# Credentials
CLIENT_ID="<keycloak-client-id>"
CLIENT_SECRET="<keycloak-client-secret>"

# Fetch a token
TOKEN_URL = KEYCLOAK_URL + "/realms/tugraz-vpu/protocol/openid-connect/token"
r = requests.post(
    TOKEN_URL, auth=(CLIENT_ID, CLIENT_SECRET),
    data={'grant_type': 'client_credentials'})
r.raise_for_status()
access_token = r.json()["access_token"]

# Make a signing request
with open(PDF_IN_PATH, 'rb') as h:
    with open(PDF2_IN_PATH, 'rb') as h2:
        r = requests.post(
            API_URL + "/esign/qualified-batch-signing-requests",
            headers={'Authorization': 'Bearer ' + access_token},
            files=[
                ('files[]', (PDF_IN_PATH, h)),
                ('files[]', (PDF2_IN_PATH, h2)),
            ],
            data=[
                ('requests[]', f'{{"profile": "{ESIGN_PROFILE}"}}'),
                ('requests[]', f'{{"profile": "{ESIGN_PROFILE}"}}'),
            ])
        r.raise_for_status()
        response = r.json()

print("Please verify your identity by visiting the following URL:")
print(response["url"])

# Wait for the user to verify their identity
code = input("Enter code to continue: ")
r = requests.get(
    API_URL + f"/esign/qualified-batch-signing-results/{quote(code)}",
    headers={'Authorization': 'Bearer ' + access_token})
r.raise_for_status()
response = r.json()

# Write the signed files to disk
for i, document in enumerate(response["documents"]):
    with urlopen(document["contentUrl"]) as h:
        with open(f"signed-{i}.pdf", 'wb') as wh:
            wh.write(h.read())
```