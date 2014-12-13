<?php
/**
 * Application default url
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\DefaultPath;

class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * Default path parts
     *
     * @var array
     */
    protected $_parts;

    /**
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->_parts = $parts;
    }

    /**
     * Retrieve path part by key
     *
     * @param string $code
     * @return string
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
