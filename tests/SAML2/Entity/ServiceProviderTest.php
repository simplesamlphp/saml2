<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Entity;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Entity;
use SimpleSAML\SAML2\Exception\MetadataNotFoundException;
use SimpleSAML\SAML2\Exception\Protocol\RequestDeniedException;
use SimpleSAML\SAML2\Exception\Protocol\ResourceNotRecognizedException;
use SimpleSAML\SAML2\Metadata;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\Test\SAML2\MockMetadataProvider;
use SimpleSAML\Test\SAML2\MockStateProvider;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @package simplesamlphp\saml2
 */
#[CoversClass(Entity\ServiceProvider::class)]
final class ServiceProviderTest extends TestCase
{
    protected static Metadata\ServiceProvider $spMetadata;
    protected static Metadata\IdentityProvider $idpMetadata;
    protected static ServerRequest $validResponse;
    protected static ServerRequest $validUnsolicitedResponse;
    protected static ServerRequest $validSolicitedResponseAsymmetricEncryptedSignedResponse;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$spMetadata = new Metadata\ServiceProvider(
            entityId: 'https://simplesamlphp.org/sp/metadata',
            assertionConsumerService: [
                AssertionConsumerService::fromArray([
                    'Binding' => C::BINDING_HTTP_POST,
                    'Location' => 'https://example.org/metadata',
                    'Index' => 0,
                ]),
            ],
            wantAssertionsSigned: true,
        );

        self::$idpMetadata = new Metadata\IdentityProvider(
            entityId: 'https://simplesamlphp.org/idp/metadata',
            validatingKeys: [
                PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
                PEMCertificatesMock::getPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY),
                PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
            ],
            decryptionKeys: [
                PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
            ],
        );

        /** A valid solicited signed response with a signed assertion */
        $q = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiIENvbnNlbnQ9Imh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvc3AvbWV0YWRhdGEiIERlc3RpbmF0aW9uPSJodHRwczovL2V4YW1wbGUub3JnL21ldGFkYXRhIiBJRD0iYWJjMTIzIiBJblJlc3BvbnNlVG89IlBIUFVuaXQiIElzc3VlSW5zdGFudD0iMjAyNC0wNy0yNVQyMjo0NDoyMVoiIFZlcnNpb249IjIuMCI+PHNhbWw6SXNzdWVyIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphc3NlcnRpb24iPmh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvaWRwL21ldGFkYXRhPC9zYW1sOklzc3Vlcj48ZHM6U2lnbmF0dXJlIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj48ZHM6U2lnbmVkSW5mbz48ZHM6Q2Fub25pY2FsaXphdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPjxkczpTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNyc2Etc2hhNTEyIi8+PGRzOlJlZmVyZW5jZSBVUkk9IiNhYmMxMjMiPjxkczpUcmFuc2Zvcm1zPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSIvPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48L2RzOlRyYW5zZm9ybXM+PGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTUxMiIvPjxkczpEaWdlc3RWYWx1ZT4rV2YzTXpjNFZ1dlFJUEpnbEdIeUhnR0l0clc0S3cwTFdJWXo4SitYcGZtTG5taEdwQkRUYTllTjhrYWN3Qm1wdkhXNkFsOHc5SDJNcjhrZjNPbThKZz09PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPmRTY2FLUHVZUnhRbEdLSHVqWnJtZUZkb1M3Y2F1aXNvMGZ3Q2gvN0s0VDFjcHdaTUNad0R5SU1qMGlzVlJycmVZM2FqSXpRTnNSVy9uWVRFd0FFWkloM3NOWGdJcW5vWmg3dng4SUN4TEo1cGVNL215citGMGlMbTk5YWs3U0FmN2FYV25McmpxWjBVOTVUMjlrd0plcXE3MzM2eHlycm9mKzZPbzhvdkN2cz08L2RzOlNpZ25hdHVyZVZhbHVlPjwvZHM6U2lnbmF0dXJlPjxzYW1scDpTdGF0dXM+PHNhbWxwOlN0YXR1c0NvZGUgVmFsdWU9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpzdGF0dXM6U3VjY2VzcyIvPjwvc2FtbHA6U3RhdHVzPjxzYW1sOkFzc2VydGlvbiB4bWxuczpzYW1sPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YXNzZXJ0aW9uIiBJRD0iXzkzYWY2NTUyMTk0NjRmYjQwM2IzNDQzNmNmYjBjNWNiMWQ5YTU1MDIiIElzc3VlSW5zdGFudD0iMTk3MC0wMS0wMVQwMTozMzozMVoiIFZlcnNpb249IjIuMCI+PHNhbWw6SXNzdWVyPnVybjp4LXNpbXBsZXNhbWxwaHA6aXNzdWVyPC9zYW1sOklzc3Vlcj48ZHM6U2lnbmF0dXJlIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj48ZHM6U2lnbmVkSW5mbz48ZHM6Q2Fub25pY2FsaXphdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPjxkczpTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNyc2Etc2hhMjU2Ii8+PGRzOlJlZmVyZW5jZSBVUkk9IiNfOTNhZjY1NTIxOTQ2NGZiNDAzYjM0NDM2Y2ZiMGM1Y2IxZDlhNTUwMiI+PGRzOlRyYW5zZm9ybXM+PGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNlbnZlbG9wZWQtc2lnbmF0dXJlIi8+PGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPjwvZHM6VHJhbnNmb3Jtcz48ZHM6RGlnZXN0TWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjc2hhMjU2Ii8+PGRzOkRpZ2VzdFZhbHVlPkoxWnNyOTRVU2FXaEhDVmNnamdnM3dHZFBHWTViVkNOeW16cEx4Yk1XekE9PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPlJIcmxHQVdSN3RROEM4T3l6cVp4WmRlU2NiRjN1QnZJdU13RG5GdXdYcjA4OCthNnBJdlRqUTNtM0syUytwM2NGNWRxS2ZiRUd5UDBmbGRpZFNtdVVLU1B2V1hTVm14bFlONldOKzBtbEVYV08rNUduN0drTXFseHlrQTAvWDlZL3AvcmYydGhEQTJPN2dnQmRrRDlsMlFUbzNYYU1CZXZtUmgwcHl4d0lNZz08L2RzOlNpZ25hdHVyZVZhbHVlPjwvZHM6U2lnbmF0dXJlPjxzYW1sOlN1YmplY3Q+PHNhbWw6TmFtZUlEIEZvcm1hdD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOm5hbWVpZC1mb3JtYXQ6dHJhbnNpZW50IiBTUE5hbWVRdWFsaWZpZXI9Imh0dHBzOi8vc3AuZXhhbXBsZS5vcmcvYXV0aGVudGljYXRpb24vc3AvbWV0YWRhdGEiPlNvbWVOYW1lSURWYWx1ZTwvc2FtbDpOYW1lSUQ+PHNhbWw6U3ViamVjdENvbmZpcm1hdGlvbiBNZXRob2Q9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpjbTpiZWFyZXIiPjxzYW1sOk5hbWVJRCBGb3JtYXQ9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpuYW1laWQtZm9ybWF0OnRyYW5zaWVudCIgU1BOYW1lUXVhbGlmaWVyPSJodHRwczovL3NwLmV4YW1wbGUub3JnL2F1dGhlbnRpY2F0aW9uL3NwL21ldGFkYXRhIj5Tb21lT3RoZXJOYW1lSURWYWx1ZTwvc2FtbDpOYW1lSUQ+PHNhbWw6U3ViamVjdENvbmZpcm1hdGlvbkRhdGEgSW5SZXNwb25zZVRvPSJfMTM2MDNhNjU2NWE2OTI5N2U5ODA5MTc1YjA1MmQxMTU5NjUxMjFjOCIgTm90T25PckFmdGVyPSIyMDExLTA4LTMxVDA4OjUxOjA1WiIgUmVjaXBpZW50PSJodHRwczovL3NwLmV4YW1wbGUub3JnL2F1dGhlbnRpY2F0aW9uL3NwL2NvbnN1bWUtYXNzZXJ0aW9uIi8+PC9zYW1sOlN1YmplY3RDb25maXJtYXRpb24+PC9zYW1sOlN1YmplY3Q+PHNhbWw6Q29uZGl0aW9ucyBOb3RCZWZvcmU9IjIwMTEtMDgtMzFUMDg6NTE6MDVaIiBOb3RPbk9yQWZ0ZXI9IjIwMTEtMDgtMzFUMTA6NTE6MDVaIj48c2FtbDpBdWRpZW5jZVJlc3RyaWN0aW9uPjxzYW1sOkF1ZGllbmNlPmh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvc3AvbWV0YWRhdGE8L3NhbWw6QXVkaWVuY2U+PC9zYW1sOkF1ZGllbmNlUmVzdHJpY3Rpb24+PC9zYW1sOkNvbmRpdGlvbnM+PHNhbWw6QXV0aG5TdGF0ZW1lbnQgQXV0aG5JbnN0YW50PSIyMDExLTA4LTMxVDA4OjUxOjA1WiIgU2Vzc2lvbkluZGV4PSJfOTNhZjY1NTIxOTQ2NGZiNDAzYjM0NDM2Y2ZiMGM1Y2IxZDlhNTUwMiI+PHNhbWw6U3ViamVjdExvY2FsaXR5IEFkZHJlc3M9IjEyNy4wLjAuMSIvPjxzYW1sOkF1dGhuQ29udGV4dD48c2FtbDpBdXRobkNvbnRleHRDbGFzc1JlZj51cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YWM6Y2xhc3NlczpQYXNzd29yZFByb3RlY3RlZFRyYW5zcG9ydDwvc2FtbDpBdXRobkNvbnRleHRDbGFzc1JlZj48L3NhbWw6QXV0aG5Db250ZXh0Pjwvc2FtbDpBdXRoblN0YXRlbWVudD48c2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWw6QXR0cmlidXRlIE5hbWU9InVybjp0ZXN0OlNlcnZpY2VJRCI+PHNhbWw6QXR0cmlidXRlVmFsdWUgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgeHNpOnR5cGU9InhzOmludGVnZXIiPjE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgTmFtZT0idXJuOnRlc3Q6RW50aXR5Q29uY2VybmVkSUQiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiIHhzaTp0eXBlPSJ4czppbnRlZ2VyIj4xPC9zYW1sOkF0dHJpYnV0ZVZhbHVlPjwvc2FtbDpBdHRyaWJ1dGU+PHNhbWw6QXR0cmlidXRlIE5hbWU9InVybjp0ZXN0OkVudGl0eUNvbmNlcm5lZFN1YklEIj48c2FtbDpBdHRyaWJ1dGVWYWx1ZSB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4c2k6dHlwZT0ieHM6aW50ZWdlciI+MTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjwvc2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PC9zYW1sOkFzc2VydGlvbj48L3NhbWxwOlJlc3BvbnNlPg==',
        ];
        $request = new ServerRequest('POST', 'http://tnyholm.se');
        self::$validResponse = $request->withParsedBody($q);

        /** A valid unsolicited signed response with a signed assertion */
        $q = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiIENvbnNlbnQ9Imh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvc3AvbWV0YWRhdGEiIERlc3RpbmF0aW9uPSJodHRwczovL2V4YW1wbGUub3JnL21ldGFkYXRhIiBJRD0iYWJjMTIzIiBJc3N1ZUluc3RhbnQ9IjIwMjQtMDctMjVUMjI6NTE6MzRaIiBWZXJzaW9uPSIyLjAiPjxzYW1sOklzc3VlciB4bWxuczpzYW1sPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YXNzZXJ0aW9uIj5odHRwczovL3NpbXBsZXNhbWxwaHAub3JnL2lkcC9tZXRhZGF0YTwvc2FtbDpJc3N1ZXI+PGRzOlNpZ25hdHVyZSB4bWxuczpkcz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PGRzOlNpZ25lZEluZm8+PGRzOkNhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxkc2lnLW1vcmUjcnNhLXNoYTUxMiIvPjxkczpSZWZlcmVuY2UgVVJJPSIjYWJjMTIzIj48ZHM6VHJhbnNmb3Jtcz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI2VudmVsb3BlZC1zaWduYXR1cmUiLz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+PC9kczpUcmFuc2Zvcm1zPjxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNzaGE1MTIiLz48ZHM6RGlnZXN0VmFsdWU+Sjk4U0t2K1NDdXFzakY0N0s3VmxXKzVKZUZkZTRCL29aOU5ac2hzM1N3VWE0ZjRXSW05ZkdaK0hOMi9MTFRBemhSWVZHeHVIUFNjUUd2WUV5Unc1cXc9PTwvZHM6RGlnZXN0VmFsdWU+PC9kczpSZWZlcmVuY2U+PC9kczpTaWduZWRJbmZvPjxkczpTaWduYXR1cmVWYWx1ZT5KUnpad1FvMWZic0R0L0FWcVZEcHU5WjVXNnNLVi9YZmtyUlhLOE84MzRaSzVDYzZHTWFBbHdnYnlaWFBZbDhsNWthSGY5eHNxWXBMT25NbzdoY0c1R2hJaHJ1QzFEK1NPUzlRdUJsSDF3ckhvdmhLNVJQWUhZUVh0NUh2UjJQdGhLQ21VclJLVE8vRkptVGQvZHN3TUt6czZCNzk5VnRuSzIwTllrNTdrc2s9PC9kczpTaWduYXR1cmVWYWx1ZT48L2RzOlNpZ25hdHVyZT48c2FtbHA6U3RhdHVzPjxzYW1scDpTdGF0dXNDb2RlIFZhbHVlPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6c3RhdHVzOlN1Y2Nlc3MiLz48L3NhbWxwOlN0YXR1cz48c2FtbDpBc3NlcnRpb24geG1sbnM6c2FtbD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmFzc2VydGlvbiIgSUQ9Il85M2FmNjU1MjE5NDY0ZmI0MDNiMzQ0MzZjZmIwYzVjYjFkOWE1NTAyIiBJc3N1ZUluc3RhbnQ9IjE5NzAtMDEtMDFUMDE6MzM6MzFaIiBWZXJzaW9uPSIyLjAiPjxzYW1sOklzc3Vlcj51cm46eC1zaW1wbGVzYW1scGhwOmlzc3Vlcjwvc2FtbDpJc3N1ZXI+PGRzOlNpZ25hdHVyZSB4bWxuczpkcz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PGRzOlNpZ25lZEluZm8+PGRzOkNhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxkc2lnLW1vcmUjcnNhLXNoYTI1NiIvPjxkczpSZWZlcmVuY2UgVVJJPSIjXzkzYWY2NTUyMTk0NjRmYjQwM2IzNDQzNmNmYjBjNWNiMWQ5YTU1MDIiPjxkczpUcmFuc2Zvcm1zPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSIvPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48L2RzOlRyYW5zZm9ybXM+PGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTI1NiIvPjxkczpEaWdlc3RWYWx1ZT5KMVpzcjk0VVNhV2hIQ1ZjZ2pnZzN3R2RQR1k1YlZDTnltenBMeGJNV3pBPTwvZHM6RGlnZXN0VmFsdWU+PC9kczpSZWZlcmVuY2U+PC9kczpTaWduZWRJbmZvPjxkczpTaWduYXR1cmVWYWx1ZT5SSHJsR0FXUjd0UThDOE95enFaeFpkZVNjYkYzdUJ2SXVNd0RuRnV3WHIwODgrYTZwSXZUalEzbTNLMlMrcDNjRjVkcUtmYkVHeVAwZmxkaWRTbXVVS1NQdldYU1ZteGxZTjZXTiswbWxFWFdPKzVHbjdHa01xbHh5a0EwL1g5WS9wL3JmMnRoREEyTzdnZ0Jka0Q5bDJRVG8zWGFNQmV2bVJoMHB5eHdJTWc9PC9kczpTaWduYXR1cmVWYWx1ZT48L2RzOlNpZ25hdHVyZT48c2FtbDpTdWJqZWN0PjxzYW1sOk5hbWVJRCBGb3JtYXQ9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpuYW1laWQtZm9ybWF0OnRyYW5zaWVudCIgU1BOYW1lUXVhbGlmaWVyPSJodHRwczovL3NwLmV4YW1wbGUub3JnL2F1dGhlbnRpY2F0aW9uL3NwL21ldGFkYXRhIj5Tb21lTmFtZUlEVmFsdWU8L3NhbWw6TmFtZUlEPjxzYW1sOlN1YmplY3RDb25maXJtYXRpb24gTWV0aG9kPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6Y206YmVhcmVyIj48c2FtbDpOYW1lSUQgRm9ybWF0PSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6bmFtZWlkLWZvcm1hdDp0cmFuc2llbnQiIFNQTmFtZVF1YWxpZmllcj0iaHR0cHM6Ly9zcC5leGFtcGxlLm9yZy9hdXRoZW50aWNhdGlvbi9zcC9tZXRhZGF0YSI+U29tZU90aGVyTmFtZUlEVmFsdWU8L3NhbWw6TmFtZUlEPjxzYW1sOlN1YmplY3RDb25maXJtYXRpb25EYXRhIEluUmVzcG9uc2VUbz0iXzEzNjAzYTY1NjVhNjkyOTdlOTgwOTE3NWIwNTJkMTE1OTY1MTIxYzgiIE5vdE9uT3JBZnRlcj0iMjAxMS0wOC0zMVQwODo1MTowNVoiIFJlY2lwaWVudD0iaHR0cHM6Ly9zcC5leGFtcGxlLm9yZy9hdXRoZW50aWNhdGlvbi9zcC9jb25zdW1lLWFzc2VydGlvbiIvPjwvc2FtbDpTdWJqZWN0Q29uZmlybWF0aW9uPjwvc2FtbDpTdWJqZWN0PjxzYW1sOkNvbmRpdGlvbnMgTm90QmVmb3JlPSIyMDExLTA4LTMxVDA4OjUxOjA1WiIgTm90T25PckFmdGVyPSIyMDExLTA4LTMxVDEwOjUxOjA1WiI+PHNhbWw6QXVkaWVuY2VSZXN0cmljdGlvbj48c2FtbDpBdWRpZW5jZT5odHRwczovL3NpbXBsZXNhbWxwaHAub3JnL3NwL21ldGFkYXRhPC9zYW1sOkF1ZGllbmNlPjwvc2FtbDpBdWRpZW5jZVJlc3RyaWN0aW9uPjwvc2FtbDpDb25kaXRpb25zPjxzYW1sOkF1dGhuU3RhdGVtZW50IEF1dGhuSW5zdGFudD0iMjAxMS0wOC0zMVQwODo1MTowNVoiIFNlc3Npb25JbmRleD0iXzkzYWY2NTUyMTk0NjRmYjQwM2IzNDQzNmNmYjBjNWNiMWQ5YTU1MDIiPjxzYW1sOlN1YmplY3RMb2NhbGl0eSBBZGRyZXNzPSIxMjcuMC4wLjEiLz48c2FtbDpBdXRobkNvbnRleHQ+PHNhbWw6QXV0aG5Db250ZXh0Q2xhc3NSZWY+dXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmFjOmNsYXNzZXM6UGFzc3dvcmRQcm90ZWN0ZWRUcmFuc3BvcnQ8L3NhbWw6QXV0aG5Db250ZXh0Q2xhc3NSZWY+PC9zYW1sOkF1dGhuQ29udGV4dD48L3NhbWw6QXV0aG5TdGF0ZW1lbnQ+PHNhbWw6QXR0cmlidXRlU3RhdGVtZW50PjxzYW1sOkF0dHJpYnV0ZSBOYW1lPSJ1cm46dGVzdDpTZXJ2aWNlSUQiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiIHhzaTp0eXBlPSJ4czppbnRlZ2VyIj4xPC9zYW1sOkF0dHJpYnV0ZVZhbHVlPjwvc2FtbDpBdHRyaWJ1dGU+PHNhbWw6QXR0cmlidXRlIE5hbWU9InVybjp0ZXN0OkVudGl0eUNvbmNlcm5lZElEIj48c2FtbDpBdHRyaWJ1dGVWYWx1ZSB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4c2k6dHlwZT0ieHM6aW50ZWdlciI+MTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjxzYW1sOkF0dHJpYnV0ZSBOYW1lPSJ1cm46dGVzdDpFbnRpdHlDb25jZXJuZWRTdWJJRCI+PHNhbWw6QXR0cmlidXRlVmFsdWUgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgeHNpOnR5cGU9InhzOmludGVnZXIiPjE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48L3NhbWw6QXR0cmlidXRlU3RhdGVtZW50Pjwvc2FtbDpBc3NlcnRpb24+PC9zYW1scDpSZXNwb25zZT4=',
        ];
        $request = new ServerRequest('POST', 'http://tnyholm.se');
        self::$validUnsolicitedResponse = $request->withParsedBody($q);

        /** A valid solicited signed response with an asymmetric encrypted signed assertion */
        $q = [
            'SAMLResponse' => 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiIENvbnNlbnQ9Imh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvc3AvbWV0YWRhdGEiIERlc3RpbmF0aW9uPSJodHRwczovL2V4YW1wbGUub3JnL21ldGFkYXRhIiBJRD0iYWJjMTIzIiBJblJlc3BvbnNlVG89IlBIUFVuaXQiIElzc3VlSW5zdGFudD0iMjAyNC0wNy0yN1QyMDozMzoxNFoiIFZlcnNpb249IjIuMCI+PHNhbWw6SXNzdWVyIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphc3NlcnRpb24iPmh0dHBzOi8vc2ltcGxlc2FtbHBocC5vcmcvaWRwL21ldGFkYXRhPC9zYW1sOklzc3Vlcj48ZHM6U2lnbmF0dXJlIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj48ZHM6U2lnbmVkSW5mbz48ZHM6Q2Fub25pY2FsaXphdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPjxkczpTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNyc2Etc2hhNTEyIi8+PGRzOlJlZmVyZW5jZSBVUkk9IiNhYmMxMjMiPjxkczpUcmFuc2Zvcm1zPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSIvPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48L2RzOlRyYW5zZm9ybXM+PGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3NoYTUxMiIvPjxkczpEaWdlc3RWYWx1ZT5tUjY3alRGeHI0V1NJSkoycnBsNExtYUlhUHhXd0s2ZmVlS1ViM0xxQ2xNTmhockZVUVYyNlRhdW5lR2N6ak9PT3lZOUFDNFRqckhRbG9ockxoS1h6Zz09PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPk5xU0xwYitTaEMvVnlmZjI5WTR4N0I4NUxJZ1U4amZSMDM2R1ZtVWJXTW52WTJlb1ZSeHFVeWV3Y2QxejRHNlZMaVVPK2dZQ0l6cTYwZnByNm1JNFRvRUdRTy9JckpMUTNKZmw4ZnpqbFJvcEpwSkVnazlXMWRBVVcwQkIrR1BnWEpOeWRPREFZQUNKcHpDZ0hwemw2eGZIRFErL0llV29hblVxZDlWUU1FTT08L2RzOlNpZ25hdHVyZVZhbHVlPjwvZHM6U2lnbmF0dXJlPjxzYW1scDpTdGF0dXM+PHNhbWxwOlN0YXR1c0NvZGUgVmFsdWU9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpzdGF0dXM6U3VjY2VzcyIvPjwvc2FtbHA6U3RhdHVzPjxzYW1sOkVuY3J5cHRlZEFzc2VydGlvbiB4bWxuczpzYW1sPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YXNzZXJ0aW9uIj48eGVuYzpFbmNyeXB0ZWREYXRhIHhtbG5zOnhlbmM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jIyIgVHlwZT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjRWxlbWVudCI+PHhlbmM6RW5jcnlwdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDl4bWxlbmMxMSNhZXMyNTYtZ2NtIi8+PGRzOktleUluZm8geG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPjx4ZW5jOkVuY3J5cHRlZEtleT48eGVuYzpFbmNyeXB0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjcnNhLW9hZXAtbWdmMXAiLz48eGVuYzpDaXBoZXJEYXRhPjx4ZW5jOkNpcGhlclZhbHVlPm1vK1VWaDU5aWFmNGg3eVQ3UyttbHpTNGtvRVFsNVFXM2R2Zk94ZlpqbW1QaFlCMERhYjV0R1RCY01NeXByZnFGNmJRYWVaUE4yNDBIWThScEN0VHhsN1BNbWRjclU4ck1wZk56Sk5VVkRQUDdTRzc5RXlxM1BxZXgwaE5BYnFsK25YRWtBOEJLRnZ1bzc5WGlUTnlhWFpqdmR5dTI1NnF5RVFXREhhTENxND08L3hlbmM6Q2lwaGVyVmFsdWU+PC94ZW5jOkNpcGhlckRhdGE+PC94ZW5jOkVuY3J5cHRlZEtleT48L2RzOktleUluZm8+PHhlbmM6Q2lwaGVyRGF0YT48eGVuYzpDaXBoZXJWYWx1ZT5XdjFzNC8rYjgrMFZtc1c5WmwvUm9reUF5WmNuYUhVS0ZJc1NhVFY3RXlKM2U5VUV4akNwcTgvMDd3UFdZR25laFg5Vk4yaDJtUzlpMGFYakdSeUd5eHhScnZZMVlkRGNvVEI0U1dTUnJqdEx4VkFjRUx4cXd5V0w4SXRnRElKTzYyaXNrcktqc3hiV1o2T0VyZDlXOEFGRkNBVC9SS0t2TDYrcDFnZThRQUIzWFdtcU1pSUdHaFBVTzVjbGVFQXZjQ04wQlhhdEh5YndZNlErK0tGZy9SeFAzQmVkNW95cmc0LzFpTmc1M2dYSDg2S0h3bXA1T0RhbWcyaU13cndzRzNqY0FFVVM1N1VFUTBGcGZuSVpucE5LL0N0a0t3aFJDMElOdFNDdmJPSW1vR2dKNVRMWksvaCtBV1k0Y3FreFFscXFvM0hHNVk5UHVHOGpEZUVuRC9JWVhEMi9zRllSTS96b0FZbXdFSDZ4Ukp0VkxVMm1rNnN1Z2VqUHU1MURrRkNmTy9vRWlQV3k2L0JsR3gveVBMWVorOGx0KzIzajNBQVV6Y1FiTUl3R09IVE1sZ1VZTGlaV3hadlFjSUdTQWJaNSt0WmRlQnhYcnROMVREMS9OR2pseC9WYk5SOGNKM2hpOXFBSmJBdkN4Qjl5aktDYnFDM1ppRG9OUVk0VjV2UDBIWnIvQUZUZFlQUTA2YkpIdVFBNUczd0FpTTZNRm9CMWVGN3NoYUZKLzJPOXZMcWdTUkhhQVJjYVlUZmZJaVRrS2xmMGhwU0F6RGJLN2s3R2lBRHI1NXZwemhjR3dtOSs0SHBXK3M3aUV2UUtZa0FhOVI0U0MxRWZFeURieDJid3FONlk3RFI0aEdjeDcvSkFEYWpuVEQ0MW4reHdKaFByRndrTUp1Q1FoNVBCbE9jQm1xSWs5c1NYVnczeGhyL3lCV1BqV040WE0vL1FEdE9CUEdGdG8vcnNNVVFtWmFVT3hGMFRIRVl4U3k4czhNM1ZQVDJxeW9La3RsTDM1cjJqMlBrRm9HQjJLMVViNlgvejBkWGhsdTRHb2lkUjkwNU9GTTk2Y29CY1c1NFkxdG45NmZNdG1xU1JkU1dUenpqQm4vZGZGalhldGJGV1VzdGhzMktTOGVxdHM1Y3hIQ2xIVDFWYTZDbytHR1RoQWxweDF2aHB4cytXUXJjTUlyYjlmTHkwYjhBTERqNGF6K1l2ZVNrSXBldHVGbDJJWHNsdm0vbkJ3WUMwTHkzQ1psWjhwdzZtb0tjZlN6T2J5d2Iwbytaazd1Y3F2ZzA5cmg5QkJpZlYzZTNoRmZld1F0c0EzWUh1dG5aWmVDc0xKWGxjdG5SVThuK2pLVHpQU2xKbGc3bzhGKytpbGlYRmdhYzlvekN1SUp4Vm1sUGJKZzVScEpVRWJEWm1XSndrSmlHZU1rY2NLVG9HS29FSjNXcmdORW1zTHNQMWpLalpMOW9MdzV6OGFHNHJuOUNBVHM5MVQ2dlpUbTBOTkhZTHRrTENrSURmalFac1MzMm5CeDY0MnVkcDRiaWtwb0J1a0hIUzUvYlFkUDJrVE9LcGdCTmhva3JHTG1Udy9XM1pwMXVtdEordkozSHllQkpDRnlNWlBwandjVlllUmxtUC9LUmhKalBmRjNQakpoSDMvbUlJZFRELzI0eWZvSVFUUDBKbkpaSktsNFlSZjJ0SEFnRVF5SW81dndJL3MwelhuQ0ZsMkpxWnZ1ZjZ6MEozQjJLZkJtcFMycjQrbDBOS3U0Z1NKZENVZ3RDRy9Mc281bmpJSnhCSDJ0L0N1WGJNRUtXTXplMUZrbSsxWWUyUHY4cG9EMTJBTyttMU9ZdnlaN2FMM1ZaalY5M3JvYWlvN2tYNzQ1QWI2V1pxK0x1WDJwVktOTzFzeC9LV25sQ1duSnNwZG5KYjJReUR5MzYxTEpZWUJUREMxblBNNk8vbVcyQ2doczNDbHdGdm9nZzFVR05kd2FrVGxjRis5d29VVmtURVZtN1Noa2NXOXhmdjRHZlZwd3JaYnJ0MEVYdm5BYndaQkJKcjI4WTlreDNrQW1td2l3ZjJyMU9IQlI3NXJFSkFBejNJY0JvbzRwOTZVZS9YcE1ZbVYyeE94UUVaM0VRM1JVZ0ZmQ21UdGY0VVhqbVRLdzNRYU5aZmZGUDNtYkhPYkM2SG9RbjBQYmxMSnU0WWJYZWp5WURBRjA5bjRxSmYydElSK0UrK2FmdGNJb21GRkkyVlBjNDFyTC9XNWNDY3hmZ0ZTQk45VitOSmpTdk1HYVBaY1NPdUJHZFRkYmdtbjljVzRMWFVlb3EwRmwydjJnTUFuaWJ2VmlHbktkZUh3R0dIenRkbS9obytsL0M4U0N5T21JNHpLd0tDdGVoQmFIUVVRaTN3NDd3ZVJ1amROUnBxMERaTlRXUnhTUWJidW96TDlHT2JuL0dxMnBZOE9pZ3Z2ZHAwSjA3aVM1b3BmTGZoM2pVbmtwRDQ0Z1RoUGtVSzE2UEc5N2Zqb2F0aFVKbHRPVFY1TUl1enJncHBJQ3NWTVpESnJaNVlhYVZ5MmQzb1hpSGttRG1iRUhJQjZkWWdkeEhpam5RS3JiNDVKYmJrUGwrN1FFN1g0ZkNubXlva1BPZ1lscUJudjUxMGNBaEFFakhOMkc1cW9QanVqQzMzTzFPbmorVkU1VFVGM2xpa3RjQjhpNXd5T1dVbk5CdklhaUJZbmJ4VHRsTi9CN2RKOXFiMFJVWENBUHVkVWRSVkhEcHg0bzIvUHczL2F6LzhjN2tOaUN5dUJMcEJaNmlTQWplV0ZmS25YQlA5Q24wOFFPb2RoNE9aTEVDQUFiVmFSVXdtWFEzMEhGaEMzc2QxVmZ2eU82aDQ1VFdGRGluYzVnUGI2V29DR2dHOWw4bHMrM0lvZFlwTElMYWpZWDN5bGxyQjF0N1hvNVpCTS81WEgwQWtLTkdyTFN4NlVLOElTcEpXNTlyZnlnci9zZXJ4dWlWeEwvcjViQmh3aCsrT2JtamIreTdVU3NpT1BoQklHU1pEUzRSRTVqWHNWN0dsUXNZbDh0dkNxMTB2UklUNmRTNzludFdSUGJaSjlkQ01WQWczNWhmTlJ3Tzh6YXVqbUl6TjNyeXJpQTdrRDM5U213OWRuckRGV0liWHRxZnFWK3dRTnhyOHdNbmdFeGJXOWhtcDRQZDFKVDBDZHNIYXc4ZzJYVXZMZGlESDcwWmNRdkZwMHZ1NnIzVTVUODF1K0JKRXRIS0lWekRVV21menJoS3pkaVZVTHlabzRQUkF2QzE3RlIwaUFseDZ0NFBsRDhHeFFOYlJPS25IM2x2NWhEK2dRNUNBVmQ5TFVtRmlxd1pEaGFHZkhJNm5ncFAyZEV1bWpld2ZBNndLUnI1cU9YSytrRUN4bDhlWkt3MWxJOGRJQzQyMEJKM0l1L2pLajRUOFVFS0RMOVB3RjZpT3lsMG5qUEoyZDJ5UnhqbUQ5bU9obTdVWVdGVlJ4Zm9uQldNUnhxWHJ6UUJLWVVHYUU4bXhqZ0psMXhrRjlDZjRZNGV2LzVWRStqK3dzeDRMRWdXU0JWdERxRjQwcmZ1OFhBU2lXR0NDSW1tWWp5TWhERDQ4dzBFS2lFYWJsYXMwZEMvTHlXR2RUY1Vzd0lyVjFtbFdMa3hXU1lLTW80Q2VPanhnMDBzNVZnU0Vib090YitPcGVLWXNheitPLy82TnV6OS9MYjlkRnhBQU96YnJwN0R5SCtzcjF5cHBuNzRaMjd6OHRTUml4MThmeWJYYVMyczFZU0JWQ3NMT0JFcVRxR0Jqc1l3SURVRnljTmxXSW1hWjczM2Nnc0c1NlFQWWZWVGt0V01UYlNpSzBpMU1nUVREdit2eGZXM3FTTUdQdlNRYStjZ3pBckdsclZmVFY3dTVvYU5lQWZUOGk4SHFUa3N3Nk90ZTdQOC8ydWVsUzd4ZFI0YkxJaDg4ejNSQ3A0WDRqbHdldHlsMHAwZUdNQXFaZ3YyUnRDQy9xZmxsRExoMWFQU0ZZSnVpMXFOelh3R0Y4VzFzV0QraW40eS9aQXZ1aC9lRGJGcVVEck5DWWNIUlNzNmYvY2wrRmFGR0U3dHYybk5ic041NWJwcUd4UjBWdjFtSjBwS3QxZ1AxcFhsYWhibGVRL0k4cDF6OWFJb3dndEdTUlFuWjJ4cUZXYzdibCtnZWowU1ppWlhra0Z3V01kL0VRUkhOYXIvSUl1cmxpbWNROHdROWhUbkVXdzRUd2tFSFcwcXhUY21QZ0dsS2hHYXR6S0UvVUN4TW5uRlN0bnM2Z2N2MU5yV2FZWWtoNUtxM1VtOTMwZ2xGWERXRnU5WC8xZHdZZloxdkMvR2RyR2xZR0lMeUN3UGI3TzhGRVpEejBmVTJrc3NVcnRlTzVRb09jS0tyYW94anZjNXE4NWtaSW85YnRNQnFKampCREhpazdwQnZhUFZnSTR5dXlsYU5HeXdUL2ZVYStJSVR1MzlmWHVuaC9oU004RnYzRVV5U2Fad0VZdU1meTYyaFdlb0l4b3AyWWFsUXBvYm9JeVVVN1pJLzVjSjg0ZzZpdE1BTXAyZko3c2pDQjdhMHA5eDAxVldHZ3BqblZzdkRuMzNESXZmZ0F5V1RJMnVJajJKLzdSVlAwcnFzYm4yWkU0SitiT3dHeFlvZGlaY3pjaWZySURqL2tNUVRsYmhFVTAwZm9aWjN5cWdsYXZxMW5vNWtkYk1mQ1M4ZEhEcnlOczZlVnFnYmNjYnJUaUNMWEVxdmwyMSs3cGl3RXVXV3ZWSGs2L0tyY2FNTmw5MFk1dWJNcnoySGlSMEhSeGRiSHVkbnBxYWkxbFFDTDk3S1g5OGY2VUhkWDJVNHdJQmhPcVUvd095ek9BN2ljY1lMMlEyb2MzZEExRzc5UitQSms3TzdVekJHa1BSQnhsa2xmR2JzUkpaWlBIM3pRWmxIRmtzeXY3SGtxbkE2NlNtNzlWUzJxS1lGREpQUDhremxwdEo5YWNyZHFjR0hqV3Z1bEFyTEFIVWZnWFZyQ2xJZUxxOXBBUU5CQnFNNlZSQStSUE5lTTZZcFZpaUUvOVZiTUJVSHpPcEwrRlczZkdhMDdMUEJ3bTI2N2wrYUdwdlU1Q1lXRnpseTU3bWpuOWZLdHArMWg3UUVPK3VFaFNVbHEyODNGVVVFZVU2bzd0djQ4RE5vTkR1MGVJYnFlUHdYRVU5SGFxVzJCZVA0VTZlRWY4VThOMHJOQXYyazZDVlRPamlab0xRRUJsUXQvU0ErQ3RBV1JSSldxT0QzNVBxNmxUWTZ6L2hSTmltY3JTYklsQkFGa0RGNjJmQ3VaSFAvcmc1MmFMa2RwY2NXcWVxZ3hCNUNubTJibGE2UGNxa01RSHZkQk85cTNzblNRZkZDbm1oMnVHNXovMHJlL2pQU0sxWWk0TE5INUlLMTMzcGlGZkhTU04zY3lMRG1wRFpPbzhHZEoxYUIwUFVwRGtrYUtHZklJRzA5TnVmL0VkSDhRZzRvL05UNXFrWUFURnkvRDJjTFJ6VnpiY3M4YjRkRjhtWXZkM2ZGZVpwbkpQeU9jS0l5MU1oeTQzTVR5aHdDRE5Tc0dtMWhHbDVLZ01ORERvLzE2MVRKMmRHWm4vOUVROVA1em5QODd5TFlLcnRGVTlPTU1VSUJXSi9rc0VEL3RlSm8ydko1azllcCtEMTJLMXRDbzVEc3RlSVFoQkpXdzE1YUJjTjZBb092amtIaGRNby92dz09PC94ZW5jOkNpcGhlclZhbHVlPjwveGVuYzpDaXBoZXJEYXRhPjwveGVuYzpFbmNyeXB0ZWREYXRhPjwvc2FtbDpFbmNyeXB0ZWRBc3NlcnRpb24+PC9zYW1scDpSZXNwb25zZT4=',
        ];
        $request = new ServerRequest('POST', 'http://tnyholm.se');
        self::$validSolicitedResponseAsymmetricEncryptedSignedResponse = $request->withParsedBody($q);
    }


    /**
     * test that an unsolicited samlp:Response is refused by default
     */
    public function testUnsolicitedResponseIsRefusedByDefault(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
        );

        $this->expectException(RequestDeniedException::class);
        $this->expectExceptionMessage("Unsolicited responses are denied by configuration.");
        $serviceProvider->receiveResponse(self::$validUnsolicitedResponse);
    }


    /**
     * test that an unsolicited samlp:Response is allowed by config
     */
    public function testUnsolicitedResponseIsAllowedByConfig(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
            enableUnsolicited: true,
        );

        $response = $serviceProvider->receiveResponse(self::$validUnsolicitedResponse);
        $this->assertInstanceOf(Response::class, $response);
    }


    /**
     * test that samlp:Response can be received when verification is bypassed.
     */
    public function testResponseParsingBypassVerification(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
            bypassResponseVerification: true,
        );
        $response = $serviceProvider->receiveResponse(self::$validResponse);
        $this->assertInstanceOf(Response::class, $response);
    }


    /**
     * test that samlp:Response can be received when verification is enabled, but validation is bypassed.
     */
    #[Depends('testResponseParsingBypassVerification')]
    public function testResponseParsingBypassValidation(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
            bypassResponseVerification: false,
            bypassConstraintValidation: true,
        );

        $state = [
            'ExpectedIssuer' => 'https://simplesamlphp.org/idp/metadata',
        ];

        $stateProvider = new MockStateProvider();
        $stateProvider::saveState($state, 'saml:sp:sso');
        $serviceProvider->setStateProvider($stateProvider);

        $response = $serviceProvider->receiveResponse(self::$validResponse);
        $this->assertInstanceOf(Response::class, $response);
    }


    /**
     * test that samlp:Response can be received when both verification and validation are enabled.
     */
    #[Depends('testResponseParsingBypassValidation')]
    public function testResponseParsingFull(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
            bypassResponseVerification: false,
            bypassConstraintValidation: false,
        );

        $state = [
            'ExpectedIssuer' => 'https://simplesamlphp.org/idp/metadata',
        ];

        $stateProvider = new MockStateProvider();
        $stateProvider::saveState($state, 'saml:sp:sso');
        $serviceProvider->setStateProvider($stateProvider);

        $response = $serviceProvider->receiveResponse(self::$validResponse);
        $this->assertInstanceOf(Response::class, $response);
    }


    /**
     * test that samlp:Response with symmetric encrypted signed assertion
     *  can be received when both verification and validation are enabled.
     */
    #[Depends('testResponseParsingBypassValidation')]
    public function testResponseParsingFullAsymmetricEncryptedSignedAssertion(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
        );

        $state = [
            'ExpectedIssuer' => 'https://simplesamlphp.org/idp/metadata',
        ];

        $stateProvider = new MockStateProvider();
        $stateProvider::saveState($state, 'saml:sp:sso');
        $serviceProvider->setStateProvider($stateProvider);

        $response = $serviceProvider->receiveResponse(self::$validSolicitedResponseAsymmetricEncryptedSignedResponse);
        $this->assertInstanceOf(Response::class, $response);
    }


    /**
     * test that a message from an unexpected issuer is refused
     */
    #[Depends('testResponseParsingBypassVerification')]
    public function testUnknownIssuerThrowsException(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([self::$idpMetadata]),
            spMetadata: self::$spMetadata,
        );

        $state = [
            'ExpectedIssuer' => 'urn:x-simplesamlphp:sp',
        ];

        $stateProvider = new MockStateProvider();
        $stateProvider::saveState($state, 'saml:sp:sso');
        $serviceProvider->setStateProvider($stateProvider);

        $this->expectException(ResourceNotRecognizedException::class);
        $this->expectExceptionMessage("Issuer doesn't match the one the AuthnRequest was sent to.");

        $serviceProvider->receiveResponse(self::$validResponse);
    }


    /**
     * test that a message from an entity  is refused
     */
    public function testUnknownEntityThrowsException(): void
    {
        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([]),
            spMetadata: self::$spMetadata,
        );

        $this->expectException(MetadataNotFoundException::class);
        $this->expectExceptionMessage(
            "No metadata found for remote entity with entityID: https://simplesamlphp.org/idp/metadata",
        );

        $serviceProvider->receiveResponse(self::$validResponse);
    }


    /**
     * test that verifying a Response with the wrong key throws a SignatureVerificationFailedException.
     */
    public function testWrongKeyThrowsException(): void
    {
        $idpMetadata = new Metadata\IdentityProvider(
            entityId: 'https://simplesamlphp.org/idp/metadata',
            validatingKeys: [
                PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
            ],
        );

        $serviceProvider = new Entity\ServiceProvider(
            metadataProvider: new MockMetadataProvider([$idpMetadata]),
            spMetadata: self::$spMetadata,
        );

        $this->expectException(SignatureVerificationFailedException::class);
        $this->expectExceptionMessage('Signature verification failed.');

        $serviceProvider->receiveResponse(self::$validResponse);
    }
}
