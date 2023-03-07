<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Api\AuthorizationServiceInterface as IntegrationAuthorizationInterface;
use Magento\Integration\Model\IntegrationConfig;

/**
 * Plugin for @see ConfigBasedIntegrationManager model to manage resource permissions of
 * integration installed from config file
 */
class Manager
{
    /**
     * Integration service
     *
     * @var IntegrationServiceInterface
     */
    protected $_integrationService;

    /**
     * Construct Setup plugin instance
     *
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param IntegrationServiceInterface $integrationService
     * @param IntegrationConfig $integrationConfig
     */
    public function __construct(
        protected readonly IntegrationAuthorizationInterface $integrationAuthorizationService,
        IntegrationServiceInterface $integrationService,
        protected readonly IntegrationConfig $integrationConfig
    ) {
        $this->_integrationService = $integrationService;
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param ConfigBasedIntegrationManager $subject
     * @param string[] $integrationNames Name of integrations passed as array from the invocation chain
     *
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.1.0
     */
    public function afterProcessIntegrationConfig(
        ConfigBasedIntegrationManager $subject,
        $integrationNames
    ) {
        if (empty($integrationNames)) {
            return [];
        }
        /** @var array $integrations */
        $integrations = $this->integrationConfig->getIntegrations();
        foreach ($integrationNames as $name) {
            if (isset($integrations[$name])) {
                $integration = $this->_integrationService->findByName($name);
                if ($integration->getId()) {
                    $this->integrationAuthorizationService->grantPermissions(
                        $integration->getId(),
                        $integrations[$name]['resource']
                    );
                }
            }
        }
        return $integrationNames;
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param ConfigBasedIntegrationManager $subject
     * @param array $integrations integrations passed as array from the invocation chain
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcessConfigBasedIntegrations(
        ConfigBasedIntegrationManager $subject,
        $integrations
    ) {
        if (empty($integrations)) {
            return [];
        }

        foreach (array_keys($integrations) as $name) {
            $integration = $this->_integrationService->findByName($name);
            if ($integration->getId()) {
                $this->integrationAuthorizationService->grantPermissions(
                    $integration->getId(),
                    $integrations[$name]['resource']
                );
            }
        }
        return $integrations;
    }
}
