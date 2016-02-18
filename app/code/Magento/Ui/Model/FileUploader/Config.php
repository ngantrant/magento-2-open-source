<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\FileUploader;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Config extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Media Directory object (writable).
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var string
     */
    protected $imageDir;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param string $imageDir
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        $imageDir = 'design/image'
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->imageDir = $imageDir;
    }

    /**
     * @return string
     */
    public function getAbsoluteTmpMediaPath()
    {
        return $this->mediaDirectory->getAbsolutePath($this->getBaseTmpMediaPath());
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->getStoreMediaUrl() . 'tmp/' . $this->imageDir;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @return mixed
     */
    public function getStoreMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->getStoreMediaUrl() . $this->imageDir;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * Filesystem directory path of temporary images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return 'media/tmp/' . $this->imageDir;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getTmpMediaPath($filename)
    {
        return 'tmp/' . $this->imageDir . '/' . $filename;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
