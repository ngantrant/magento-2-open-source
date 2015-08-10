<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\Navigation as NavModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\Updater as ModelUpdater;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for updater tasks
 */
class StartUpdater extends AbstractActionController
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var NavModel
     */
    private $navigation;

    /**
     * @var ModelUpdater
     */
    private $updater;

    /**
     * @param Filesystem $filesystem
     * @param NavModel $navigation
     * @param ModelUpdater $updater
     */
    public function __construct(Filesystem $filesystem, NavModel $navigation, ModelUpdater $updater)
    {
        $this->filesystem = $filesystem;
        $this->navigation = $navigation;
        $this->updater = $updater;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Update action
     *
     * @return JsonModel
     */
    public function updateAction()
    {
        $postPayload = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $errorMessage = '';
        if (isset($postPayload['packages']) && is_array($postPayload['packages']) && isset($postPayload['type'])) {
            $packages = $postPayload['packages'];
            $jobType = $postPayload['type'];
            foreach ($packages as $package) {
                if (!isset($package['name'])
                    || ($jobType != 'uninstall' && !isset($package['version']))
                    || ($jobType == 'uninstall' && !isset($package['type']))
                ) {
                    $errorMessage .= 'Missing package information';
                    break;
                }
            }
            if (empty($errorMessage)) {
                $this->createTypeFlag($jobType, $postPayload['headerTitle']);
                $errorMessage .= $this->updater->createUpdaterTask(
                    $packages,
                    $jobType == 'uninstall' ? ModelUpdater::TASK_TYPE_UNINSTALL : ModelUpdater::TASK_TYPE_UPDATE
                );
            }
        } else {
            $errorMessage .= 'Invalid request';
        }
        $success = empty($errorMessage) ? true : false;
        return new JsonModel(['success' => $success, 'message' => $errorMessage]);
    }

    /**
     * Create flag to be used in Updater
     *
     * @param string $type
     * @param string $title
     * @return void
     */
    private function createTypeFlag($type, $title)
    {
        $data = [];
        $data['type'] = $type;
        $data['headerTitle'] = $title;

        $menuItems = $this->navigation->getMenuItems();
        $titles = [];
        foreach ($menuItems as $menuItem) {
            if (isset($menuItem['type']) && $menuItem['type'] === $type) {
                $titles[] = str_replace("\n", '<br />', $menuItem['title']);
            }
        }
        $data['titles'] = $titles;
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directoryWrite->writeFile('.type.json', Json::encode($data));
    }
}
