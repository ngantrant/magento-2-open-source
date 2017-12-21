<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Exception;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

/**
 * Exception for GraphQL to be thrown when user supplies invalid input
 */
class GraphQlInputException extends InputException implements \GraphQL\Error\ClientAware
{
    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * Initialize object
     *
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param boolean $isSafe
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0, $isSafe = true)
    {
        $this->isSafe = $isSafe;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * {@inheritDoc}
     */
    public function isClientSafe()
    {
        return $this->isSafe;
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory()
    {
        return 'graphql-input';
    }
}
