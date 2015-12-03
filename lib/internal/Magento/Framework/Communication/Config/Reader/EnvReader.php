<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\Communication\Config\Validator\EnvValidator;
use Magento\Framework\App\DeploymentConfig;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class EnvReader implements \Magento\Framework\Config\ReaderInterface
{
    const ENV_COMMUNICATION = 'communication';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var EnvValidator
     */
    private $envValidator;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param EnvValidator $envValidator
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        EnvValidator $envValidator
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->envValidator = $envValidator;
    }

    /**
     * Read communication configuration from env.php
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_COMMUNICATION);
        if ($configData) {
            $this->envValidator->validate($configData);
        }
        return $configData ?: [];
    }

}
