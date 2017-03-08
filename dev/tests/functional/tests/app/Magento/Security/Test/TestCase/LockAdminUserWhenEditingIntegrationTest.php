<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;

/**
 * Preconditions:
 * 1. Create admin user.
 * 2. Create integration.
 * 3. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > Extensions > Integrations.
 * 3. Start to edit existing Integration.
 * 4. Fill in all data according to data set (password is incorrect).
 * 5. Perform action 4 specified number of times.
 * 6. "You have entered an invalid password for current user." appears after each attempt.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49039
 */
class LockAdminUserWhenEditingIntegrationTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Integration grid page.
     *
     * @var IntegrationIndex
     */
    protected $integrationIndexPage;

    /**
     * Integration new page.
     *
     * @var IntegrationNew
     */
    protected $integrationNewPage;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * @var AdminAuthLogin
     */
    protected $adminAuthLogin;

    /**
     * Preparing pages for test.
     *
     * @param IntegrationIndex $integrationIndex
     * @param IntegrationNew $integrationNew
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
     */
    public function __inject(
        IntegrationIndex $integrationIndex,
        IntegrationNew $integrationNew,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->integrationIndexPage = $integrationIndex;
        $this->integrationNewPage = $integrationNew;
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Run Lock user when creating new integration test.
     *
     * @param Integration $initintegration
     * @param Integration $integration
     * @param int $attempts
     * @param User $customAdmin
     * @param string $configData
     * @return void
     */
    public function test(
        Integration $initintegration,
        Integration $integration,
        $attempts,
        User $customAdmin,
        $configData
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customAdmin->persist();
        $initintegration->persist();

        // login to backend with new user
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();

        // Steps
        $filter = ['name' => $initintegration->getName()];
        $this->integrationIndexPage->open();
        $this->integrationIndexPage->getIntegrationGrid()->searchAndOpen($filter);
        for ($i = 0; $i < $attempts; $i++) {
            $this->integrationNewPage->getIntegrationForm()->fill($integration);
            $this->integrationNewPage->getFormPageActions()->save();
        }

        // Reload page
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
