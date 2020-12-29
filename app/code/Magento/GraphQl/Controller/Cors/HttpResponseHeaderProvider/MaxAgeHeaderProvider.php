<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\Cors\HttpResponseHeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\GraphQl\Model\Cors\ConfigurationProviderInterface;
use Magento\GraphQl\Model\Cors\Validator\RequestValidatorInterface;

/**
 * Provides value for Max-Age header if CORS is enabled
 */
class MaxAgeHeaderProvider implements HeaderProviderInterface
{
    /**
     * CORS MAX AGE default value
     */
    private const GRAPH_CORS_MAX_AGE_DEFAULT = '86400';

    /**
     * @var string
     */
    private $headerName;

    /**
     * CORS configuration provider
     *
     * @var ConfigurationProviderInterface
     */
    private $corsConfiguration;

    /**
     * @var RequestValidatorInterface
     */
    private $requestValidator;

    /**
     * @param ConfigurationProviderInterface $corsConfiguration
     * @param RequestValidatorInterface $requestValidator
     * @param string $headerName
     */
    public function __construct(
        ConfigurationProviderInterface $corsConfiguration,
        RequestValidatorInterface $requestValidator,
        string $headerName
    ) {
        $this->corsConfiguration = $corsConfiguration;
        $this->headerName = $headerName;
        $this->requestValidator = $requestValidator;
    }

    /**
     * Get name of header
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->headerName;
    }

    /**
     * Check if header can be applied
     *
     * @return bool
     */
    public function canApply(): bool
    {
        return $this->requestValidator->isOriginAllowed() && $this->getValue();
    }

    /**
     * Get value for header
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->corsConfiguration->getMaxAge() ?? self::GRAPH_CORS_MAX_AGE_DEFAULT;
    }
}
