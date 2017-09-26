<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Request;

use Magento\Framework\Exception\NoSuchEntityException;

class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Process path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo)
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = $pathParts[0];

        try {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $this->storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            try {
                /** get the store by url */
                $requestUri = $request->getUri();
                if (empty($requestUri)) {
                    return $pathInfo;
                }
                $store = $this->storeManager->getStoreByUrl($requestUri);
                $storeBaseUrl = $store->getBaseUrl();
                $baseUrlUri = \Zend\Uri\UriFactory::factory($storeBaseUrl);
                $baseUrlPath = $baseUrlUri->getPath();
                $pathInfo = preg_replace('#^' . $baseUrlPath . '#', '', $pathInfo);
            } catch (NoSuchEntityException $e) {
                return $pathInfo;
            }
        }

        if ($store->isUseStoreInUrl()) {
            if (!$request->isDirectAccessFrontendName($storeCode)) {
                $this->storeManager->setCurrentStore($storeCode);
                $pathInfo = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');
                return $pathInfo;
            } elseif (!empty($storeCode)) {
                $request->setActionName('noroute');
                return $pathInfo;
            }
            return $pathInfo;
        }
        return $pathInfo;
    }
}
