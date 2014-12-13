<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->create('Magento\Framework\Filesystem');

/** @var $tmpDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
$tmpDirectory->create($tmpDirectory->getAbsolutePath());

$targetTmpFilePath = $tmpDirectory->getAbsolutePath('magento_small_image.jpg');
if (file_exists($targetTmpFilePath)) {
    unlink($targetTmpFilePath);
}
