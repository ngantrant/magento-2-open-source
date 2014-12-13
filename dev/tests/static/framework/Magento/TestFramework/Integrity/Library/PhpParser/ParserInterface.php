<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Parser for each token type
 *
 */
interface ParserInterface
{
    /**
     * Parse specific token
     *
     * @param array|string $value
     * @param int $key
     */
    public function parse($value, $key);
}
