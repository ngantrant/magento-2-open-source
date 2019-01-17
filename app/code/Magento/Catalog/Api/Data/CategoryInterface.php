<?php
/**
 * Category data interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface CategoryInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const KEY_PARENT_ID = 'parent_id';
    const KEY_NAME = 'name';
    const KEY_IS_ACTIVE = 'is_active';
    const KEY_IS_ANCHOR = 'is_anchor';
    const KEY_POSITION = 'position';
    const KEY_LEVEL = 'level';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_CREATED_AT = 'created_at';
    const KEY_PATH = 'path';
    const KEY_AVAILABLE_SORT_BY = 'available_sort_by';
    const KEY_INCLUDE_IN_MENU = 'include_in_menu';
    const KEY_PRODUCT_COUNT = 'product_count';
    const KEY_CHILDREN_DATA = 'children_data';

    const ATTRIBUTES = [
        'id',
        self::KEY_PARENT_ID,
        self::KEY_NAME,
        self::KEY_IS_ACTIVE,
        self::KEY_IS_ANCHOR,
        self::KEY_POSITION,
        self::KEY_LEVEL,
        self::KEY_UPDATED_AT,
        self::KEY_CREATED_AT,
        self::KEY_AVAILABLE_SORT_BY,
        self::KEY_INCLUDE_IN_MENU,
        self::KEY_CHILDREN_DATA,
    ];
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get parent category ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Check whether category is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Check whether category is anchor
     *
     * @return bool|null
     */
    public function getIsAnchor();

    /**
     * Set whether category is anchor
     *
     * @param bool $isAnchor
     * @return $this
     */
    public function setIsAnchor($isAnchor);

    /**
     * Get category position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get category level
     *
     * @return int|null
     */
    public function getLevel();

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level);

    /**
     * @return string|null
     */
    public function getChildren();

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     */
    public function getPath();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string[]|null
     */
    public function getAvailableSortBy();

    /**
     * @param string[]|string $availableSortBy
     * @return $this
     */
    public function setAvailableSortBy($availableSortBy);

    /**
     * @return bool|null
     */
    public function getIncludeInMenu();

    /**
     * @param bool $includeInMenu
     * @return $this
     */
    public function setIncludeInMenu($includeInMenu);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes);
}
