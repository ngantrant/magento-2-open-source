<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Magento
 * @package    \Magento\Stdlib
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Stdlib;

/**
 * Class ArrayUtils
 *
 * @package Magento\Stdlib
 */
class ArrayUtils
{
    /**
     * Sorts array with multibyte string keys
     *
     * @param  array $sort
     * @param  string $locale
     * @return array|bool
     */
    public function ksortMultibyte(array &$sort, $locale)
    {
        if (empty($sort)) {
            return false;
        }
        $oldLocale = setlocale(LC_COLLATE, "0");
        // use fallback locale if $localeCode is not available

        if (strpos($locale, '.UTF8') === false) {
            $locale .= '.UTF8';
        }

        setlocale(LC_COLLATE,  $locale, 'C.UTF-8', 'en_US.utf8');
        ksort($sort, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocale);

        return $sort;
    }

    /**
     * Decorate a plain array of arrays or objects
     * The array actually can be an object with Iterator interface
     *
     * Keys with prefix_* will be set:
     * *_is_first - if the element is first
     * *_is_odd / *_is_even - for odd/even elements
     * *_is_last - if the element is last
     *
     * The respective key/attribute will be set to element, depending on object it is or array.
     * \Magento\Object is supported.
     *
     * $forceSetAll true will cause to set all possible values for all elements.
     * When false (default), only non-empty values will be set.
     *
     * @param mixed $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return mixed
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        // check if array or an object to be iterated given
        if (!(is_array($array) || is_object($array))) {
            return $array;
        }

        $keyIsFirst = "{$prefix}is_first";
        $keyIsOdd   = "{$prefix}is_odd";
        $keyIsEven  = "{$prefix}is_even";
        $keyIsLast  = "{$prefix}is_last";

        $count  = count($array); // this will force Iterator to load
        $index  = 0;
        $isEven = false;
        foreach ($array as $key => $element) {
            if (is_object($element)) {
                $this->_decorateArrayObject($element, $keyIsFirst, (0 === $index), $forceSetAll || (0 === $index));
                $this->_decorateArrayObject($element, $keyIsOdd, !$isEven, $forceSetAll || !$isEven);
                $this->_decorateArrayObject($element, $keyIsEven, $isEven, $forceSetAll || $isEven);
                $isEven = !$isEven;
                $index++;
                $this->_decorateArrayObject($element, $keyIsLast, ($index === $count), $forceSetAll || ($index === $count));
            } elseif (is_array($element)) {
                if ($forceSetAll || (0 === $index)) {
                    $array[$key][$keyIsFirst] = (0 === $index);
                }
                if ($forceSetAll || !$isEven) {
                    $array[$key][$keyIsOdd] = !$isEven;
                }
                if ($forceSetAll || $isEven) {
                    $array[$key][$keyIsEven] = $isEven;
                }
                $isEven = !$isEven;
                $index++;
                if ($forceSetAll || ($index === $count)) {
                    $array[$key][$keyIsLast] = ($index === $count);
                }
            }
        }
        return $array;
    }

    /**
     * Mark passed object with specified flag and appropriate value.
     *
     * @param \Magento\Object $element
     * @param string $key
     * @param mixed $value
     * @param bool $isSkipped
     */
    private function _decorateArrayObject($element, $key, $value, $isSkipped)
    {
        if ($isSkipped && $element instanceof \Magento\Object) {
            $element->setData($key, $value);
        }
    }
}
