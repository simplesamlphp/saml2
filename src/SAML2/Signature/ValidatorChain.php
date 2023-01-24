<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

use function get_class;
use function sprintf;

/**
 * Allows for validation of a signature trying different validators till a validator is found
 * that can validate the signature.
 *
 * If no validation is possible an exception is thrown.
 */
class ValidatorChain implements ValidatorInterface
{
    /** @var \SimpleSAML\SAML2\Signature\ChainedValidator[] */
    private array $validators = [];


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Signature\ChainedValidator[] $validators
     */
    public function __construct(
        private LoggerInterface $logger,
        array $validators,
    ) {
        // should be done through "adder" injection in the container.
        foreach ($validators as $validator) {
            $this->appendValidator($validator);
        }
    }


    /**
     * @param \SimpleSAML\SAML2\Signature\ChainedValidator $validator
     */
    public function appendValidator(ChainedValidator $validator): void
    {
        $this->validators[] = $validator;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $signedElement
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SignedElementInterface $signedElement,
        CertificateProvider $configuration,
    ): bool {
        foreach ($this->validators as $validator) {
            if ($validator->canValidate($signedElement, $configuration)) {
                $this->logger->debug(sprintf(
                    'Validating the signed element with validator of type "%s"',
                    get_class($validator)
                ));

                return $validator->hasValidSignature($signedElement, $configuration);
            }

            $this->logger->debug(sprintf(
                'Could not validate the signed element with validator of type "%s"',
                get_class($validator)
            ));
        }

        throw new MissingConfigurationException(sprintf(
            'No certificates have been configured%s',
            $configuration->has('entityid') ? ' for "' . $configuration->get('entityid') . '"' : ''
        ));
    }
}
