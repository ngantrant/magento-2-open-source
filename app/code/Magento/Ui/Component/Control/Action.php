<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class Action
 */
class Action extends AbstractComponent implements ControlInterface
{
    const NAME = 'action';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
