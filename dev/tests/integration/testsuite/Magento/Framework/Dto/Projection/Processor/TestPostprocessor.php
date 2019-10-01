<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Projection\Processor;

use Magento\Framework\Dto\DtoProjection\ProcessorInterface;

/**
 * Test preprocessor
 */
class TestPostprocessor implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $data, array $originalData): array
    {
        $data['prop_two'] .= 'z';
        return $data;
    }
}
