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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Shipping report resource model
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Report;

class Shipping extends \Magento\Sales\Model\Resource\Report\AbstractReport
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_setResource('sales');
    }

    /**
     * Aggregate Shipping data
     *
     * @param mixed $from
     * @param mixed $to
     * @return \Magento\Sales\Model\Resource\Report\Shipping
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to   = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $this->_aggregateByOrderCreatedAt($from, $to);
        $this->_aggregateByShippingCreatedAt($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_SHIPPING_FLAG_CODE);
        return $this;
    }

    /**
     * Aggregate shipping report by order create_at as period
     *
     * @param mixed $from
     * @param mixed $to
     * @return \Magento\Sales\Model\Resource\Report\Shipping
     * @throws \Exception
     */
    protected function _aggregateByOrderCreatedAt($from, $to)
    {
        $table       = $this->getTable('sales_shipping_aggregated_order');
        $sourceTable = $this->getTable('sales_flat_order');
        $adapter     = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, 'created_at', $from, $to)
            );
            $shippingCanceled = $adapter->getIfNullSql('base_shipping_canceled', 0);
            $shippingRefunded = $adapter->getIfNullSql('base_shipping_refunded', 0);
            $columns = array(
                'period'                => $periodExpr,
                'store_id'              => 'store_id',
                'order_status'          => 'status',
                'shipping_description'  => 'shipping_description',
                'orders_count'          => new \Zend_Db_Expr('COUNT(entity_id)'),
                'total_shipping'        => new \Zend_Db_Expr(
                    "SUM((base_shipping_amount - {$shippingCanceled}) * base_to_global_rate)"
                ),
                'total_shipping_actual' => new \Zend_Db_Expr(
                    "SUM((base_shipping_invoiced - {$shippingRefunded}) * base_to_global_rate)"
                ),
            );

            $select = $adapter->select();
            $select->from($sourceTable, $columns)
                 ->where('state NOT IN (?)', array(
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    \Magento\Sales\Model\Order::STATE_NEW
                ))
                ->where('is_virtual = 0');

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array($periodExpr, 'store_id', 'status', 'shipping_description'));
            $select->having('orders_count > 0');
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
            $select->reset();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new \Zend_Db_Expr(\Magento\Core\Model\Store::DEFAULT_STORE_ID),
                'order_status'          => 'order_status',
                'shipping_description'  => 'shipping_description',
                'orders_count'          => new \Zend_Db_Expr('SUM(orders_count)'),
                'total_shipping'        => new \Zend_Db_Expr('SUM(total_shipping)'),
                'total_shipping_actual' => new \Zend_Db_Expr('SUM(total_shipping_actual)'),
            );

            $select->from($table, $columns)
                ->where('store_id != ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'order_status', 'shipping_description'));
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
        } catch (\Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        $adapter->commit();
        return $this;
    }

    /**
     * Aggregate shipping report by shipment create_at as period
     *
     * @param mixed $from
     * @param mixed $to
     * @return \Magento\Sales\Model\Resource\Report\Shipping
     * @throws \Exception
     */
    protected function _aggregateByShippingCreatedAt($from, $to)
    {
        $table       = $this->getTable('sales_shipping_aggregated');
        $sourceTable = $this->getTable('sales_flat_invoice');
        $orderTable  = $this->getTable('sales_flat_order');
        $adapter     = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeRelatedSelect(
                    $sourceTable, $orderTable, array('order_id'=>'entity_id'),
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('source_table' => $sourceTable),
                    'source_table.created_at', $from, $to
                )
            );
            $shippingCanceled = $adapter->getIfNullSql('order_table.base_shipping_canceled', 0);
            $shippingRefunded = $adapter->getIfNullSql('order_table.base_shipping_refunded', 0);
            $columns = array(
                'period'                => $periodExpr,
                'store_id'              => 'order_table.store_id',
                'order_status'          => 'order_table.status',
                'shipping_description'  => 'order_table.shipping_description',
                'orders_count'          => new \Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'total_shipping'        => new \Zend_Db_Expr('SUM((order_table.base_shipping_amount - '
                    . "{$shippingCanceled}) * order_table.base_to_global_rate)"),
                'total_shipping_actual' => new \Zend_Db_Expr('SUM((order_table.base_shipping_invoiced - '
                    . "{$shippingRefunded}) * order_table.base_to_global_rate)"),
            );

            $select = $adapter->select();
            $select->from(array('source_table' => $sourceTable), $columns)->joinInner(
                array('order_table' => $orderTable),
                $adapter->quoteInto(
                    'source_table.order_id = order_table.entity_id AND order_table.state != ?',
                    \Magento\Sales\Model\Order::STATE_CANCELED
                ),
                array()
            )
            ->useStraightJoin();

            $filterSubSelect = $adapter->select()
                ->from(array('filter_source_table' => $sourceTable), 'MIN(filter_source_table.entity_id)')
                ->where('filter_source_table.order_id = source_table.order_id');

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->where('source_table.entity_id = (?)', new \Zend_Db_Expr($filterSubSelect));
            unset($filterSubSelect);

            $select->group(array(
                $periodExpr,
                'order_table.store_id',
                'order_table.status',
                'order_table.shipping_description'
            ));

            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
            $select->reset();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new \Zend_Db_Expr(\Magento\Core\Model\Store::DEFAULT_STORE_ID),
                'order_status'          => 'order_status',
                'shipping_description'  => 'shipping_description',
                'orders_count'          => new \Zend_Db_Expr('SUM(orders_count)'),
                'total_shipping'        => new \Zend_Db_Expr('SUM(total_shipping)'),
                'total_shipping_actual' => new \Zend_Db_Expr('SUM(total_shipping_actual)'),
            );

            $select->from($table, $columns)
                ->where('store_id != ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'order_status', 'shipping_description'));
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $adapter->query($insertQuery);
        } catch (\Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        $adapter->commit();
        return $this;
    }
}
