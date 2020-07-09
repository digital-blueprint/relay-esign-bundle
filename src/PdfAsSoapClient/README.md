The classes were generated with https://github.com/wsdl2phpgenerator/wsdl2phpgenerator
and then (heavily) adjusted.

We include the wsdl.xml directly here because otherwise we can't use the PHP soap client
in our unit tests without hitting an actual soap service.

The xml was created by using "pdf-as-web/services/wsverify?wsdl", inlining the
imports and setting soap:address location to an empty string because we override
that at runtime.