<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Translate\Inline;

interface StateInterface
{
    /**
     * Disable inline translation
     *
     * @return void
     */
    public function disable();

    /**
     * Enable inline translation
     *
     * @return void
     */
    public function enable();

    /**
     * Check if inline translation enabled/disabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Suspend inline translation
     *
     * Store current inline translation status
     * and apply new status or disable inline translation.
     *
     * @param bool $status
     * @return void
     */
    public function suspend($status = false);

    /**
     * Disable inline translation
     *
     * Restore inline translation status
     * or apply new status.
     *
     * @param bool $status
     * @return void
     */
    public function resume($status = true);
}
