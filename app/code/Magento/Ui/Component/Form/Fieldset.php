<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Fieldset
 */
class Fieldset extends AbstractComponent
{
    const NAME = 'fieldset';

    /**
     * @var bool
     */
    protected $collapsible = false;

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
