<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Widget;

use Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\CategoriesJson as PromoWidgetCategoriesJson;

/**
 * Class for generation of JSON for building tree catalog.
 *
 * Examples of use:
 * \Magento\Catalog\Block\Adminhtml\Category\Tree::getLoadTreeUrl
 * \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::getLoadTreeUrl
 */
class CategoriesJson extends PromoWidgetCategoriesJson
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SalesRule::quote';
}
