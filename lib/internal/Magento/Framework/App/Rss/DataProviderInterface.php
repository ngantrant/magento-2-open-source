<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Rss;

/**
 * Interface DataProviderInterface
 * @package Magento\Framework\App\Rss
 */
interface DataProviderInterface
{
    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed();

    /**
     * Get RSS feed items
     *
     * @return array
     */
    public function getRssData();

    /**
     * @return string
     */
    public function getCacheKey();

    /**
     * @return int
     */
    public function getCacheLifetime();

    /**
     * Get information about all feeds this Data Provider is responsible for
     *
     * @return array
     */
    public function getFeeds();

    /**
     * @return bool
     */
    public function isAuthRequired();
}
