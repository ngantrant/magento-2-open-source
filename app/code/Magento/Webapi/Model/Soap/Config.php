<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Model\Soap;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception;
use Magento\Webapi\Model\ServiceMetadata;

/**
 * Webapi Config Model for Soap.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config
{
    /**
     * List of SOAP operations available in the system
     *
     * @var array
     */
    protected $soapOperations;

    /**
     * Initialize dependencies.
     *
     * @param ObjectManagerInterface $objectManager
     * @param Registry $registry
     * @param ServiceMetadata $serviceMetadata
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly Registry $registry,
        protected readonly ServiceMetadata $serviceMetadata
    ) {
    }

    /**
     * Retrieve the list of SOAP operations available in the system
     *
     * @param array $requestedServices The list of requested services with their versions
     * @return array <pre>
     * array(
     *     array(
     *         'class' => $serviceClass,
     *         'method' => $serviceMethod
     *         'isSecure' => $isSecure
     *     ),
     *      ...
     * )</pre>
     */
    protected function getSoapOperations($requestedServices)
    {
        if (null == $this->soapOperations) {
            $this->soapOperations = [];
            foreach ($this->getRequestedSoapServices($requestedServices) as $serviceName => $serviceData) {
                foreach ($serviceData[ServiceMetadata::KEY_SERVICE_METHODS] as $methodData) {
                    $method = $methodData[ServiceMetadata::KEY_METHOD];
                    $class = $serviceData[ServiceMetadata::KEY_CLASS];
                    $operation = $methodData[ServiceMetadata::KEY_METHOD_ALIAS];
                    $operationName = $serviceName . ucfirst($operation);
                    $inputArraySizeLimit = $methodData[ServiceMetadata::KEY_INPUT_ARRAY_SIZE_LIMIT];
                    $this->soapOperations[$operationName] = [
                        ServiceMetadata::KEY_CLASS => $class,
                        ServiceMetadata::KEY_METHOD => $method,
                        ServiceMetadata::KEY_IS_SECURE => $methodData[ServiceMetadata::KEY_IS_SECURE],
                        ServiceMetadata::KEY_ACL_RESOURCES => $methodData[ServiceMetadata::KEY_ACL_RESOURCES],
                        ServiceMetadata::KEY_ROUTE_PARAMS => $methodData[ServiceMetadata::KEY_ROUTE_PARAMS],
                        ServiceMetadata::KEY_INPUT_ARRAY_SIZE_LIMIT => $inputArraySizeLimit,
                    ];
                }
            }
        }
        return $this->soapOperations;
    }

    /**
     * Retrieve service method information, including service class, method name, and isSecure attribute value.
     *
     * @param string $soapOperation
     * @param array $requestedServices The list of requested services with their versions
     * @return array
     * @throws Exception
     */
    public function getServiceMethodInfo($soapOperation, $requestedServices)
    {
        $soapOperations = $this->getSoapOperations($requestedServices);
        if (!isset($soapOperations[$soapOperation])) {
            throw new Exception(
                __('Operation "%1" not found.', $soapOperation),
                0,
                Exception::HTTP_NOT_FOUND
            );
        }
        $inputArraySizeLimit = $soapOperations[$soapOperation][ServiceMetadata::KEY_INPUT_ARRAY_SIZE_LIMIT];

        return [
            ServiceMetadata::KEY_CLASS => $soapOperations[$soapOperation][ServiceMetadata::KEY_CLASS],
            ServiceMetadata::KEY_METHOD => $soapOperations[$soapOperation][ServiceMetadata::KEY_METHOD],
            ServiceMetadata::KEY_IS_SECURE => $soapOperations[$soapOperation][ServiceMetadata::KEY_IS_SECURE],
            ServiceMetadata::KEY_ACL_RESOURCES => $soapOperations[$soapOperation][ServiceMetadata::KEY_ACL_RESOURCES],
            ServiceMetadata::KEY_ROUTE_PARAMS => $soapOperations[$soapOperation][ServiceMetadata::KEY_ROUTE_PARAMS],
            ServiceMetadata::KEY_INPUT_ARRAY_SIZE_LIMIT => $inputArraySizeLimit,
        ];
    }

    /**
     * Retrieve the list of services corresponding to specified services and their versions.
     *
     * @param array $requestedServices array('FooBarV1', 'OtherBazV2', ...)
     * @return array Filtered list of services
     */
    public function getRequestedSoapServices(array $requestedServices)
    {
        $services = [];
        $soapServicesConfig = $this->serviceMetadata->getServicesConfig();
        foreach ($requestedServices as $serviceName) {
            if (isset($soapServicesConfig[$serviceName])) {
                $services[$serviceName] = $soapServicesConfig[$serviceName];
            }
        }
        return $services;
    }

    /**
     * Generate SOAP operation name.
     *
     * @param string $interfaceName e.g. \Magento\Catalog\Api\ProductInterfaceV1
     * @param string $methodName e.g. create
     * @param string $version
     * @return string e.g. catalogProductCreate
     */
    public function getSoapOperation($interfaceName, $methodName, $version)
    {
        $serviceName = $this->serviceMetadata->getServiceName($interfaceName, $version);
        $operationName = $serviceName . ucfirst($methodName);
        return $operationName;
    }
}
