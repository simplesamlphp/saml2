# UPGRADE NOTES

## 3.3 to 4.0 

**Assertion processor BC breaking change**

The response processor in pre 4.0 releases assumed all assertions are decrypted, and caused a fatal error when the response was unsigned.
This problem was fixed in [#120](https://github.com/simplesamlphp/saml2/pull/120). 

If you are using the assertion processor as a stand-alone component, then you will have to update your code to reflect this
change, see: [e6c01fa](https://github.com/simplesamlphp/saml2/commit/e6c01fa9b0e815682e24916f03a84d245480c4a0).