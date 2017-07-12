<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Backend\Search;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/order.php
 * @magentoDataFixture Magento/Sales/_files/order_shipping_address_different_to_billing.php
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testGetResults($query, $limit, $start, $expectedResult)
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $orderIdByIncrementId = [];
        foreach (['100000001', '100000002', '100000003'] as $incrementId) {
            $orderIdByIncrementId[$incrementId] = $order->loadByIncrementId($incrementId)->getId();
        }

        /** Preconditions */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Sales\Model\Backend\Search\Order $orderSearch */
        $orderSearch = $objectManager->create(\Magento\Sales\Model\Backend\Search\Order::class);
        /** @var \Magento\Backend\Model\Search\SearchCriteria $searchCriteria */
        $searchCriteria = $objectManager->create(\Magento\Backend\Model\Search\SearchCriteria::class);
        $searchCriteria->setStart($start);
        $searchCriteria->setLimit($limit);
        $searchCriteria->setQuery($query);

        /** SUT Execution */
        $searchResults = $orderSearch->getResults($searchCriteria);

        /** Ensure that search results are correct */
        $this->assertCount(count($expectedResult), $searchResults, 'Quantity of search result items is invalid.');
        foreach ($expectedResult as $itemIndex => $expectedItem) {
            /** Validate URL to item */
            $orderIncrementId = substr($expectedItem['id'], strlen('order/1/#'));
            $this->assertContains(
                "order/view/order_id/{$orderIdByIncrementId[$orderIncrementId]}",
                $searchResults[$itemIndex]['url'],
                'Item URL is invalid.'
            );
            $expectedItem['id'] = 'order/1/' . $orderIdByIncrementId[$orderIncrementId];
            unset($searchResults[$itemIndex]['url']);

            /** Validate other item data */
            foreach ($expectedItem as $field => $value) {
                $this->assertEquals(
                    $value,
                    (string)$searchResults[$itemIndex][$field],
                    "Data of item #$itemIndex is invalid."
                );
            }
        }
    }

    public static function loadDataProvider()
    {
        return [
            'All items, first page' => [
                '10000000',
                2, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'order/1/#100000001',
                        'type' => 'Order',
                        'name' => 'Order #100000001',
                        'description' => 'firstname lastname',
                    ],
                    [
                        'id' => 'order/1/#100000002',
                        'type' => 'Order',
                        'name' => 'Order #100000002',
                        'description' => 'guest guest'
                    ]
                ],
            ],
            'All items, second page' => [
                '10000000',
                2, // Items on page
                2, // Page number
                [
                    [
                        'id' => 'order/1/#100000003',
                        'type' => 'Order',
                        'name' => 'Order #100000003',
                        'description' => 'guest guest',
                    ]
                ],
            ],
            'Search by first name, first item only' => [
                'First',
                10, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'order/1/#100000001',
                        'type' => 'Order',
                        'name' => 'Order #100000001',
                        'description' => 'firstname lastname',
                    ]
                ],
            ],
            'No results' => [
                'NotExistingOrder',
                10, // Items on page
                1, // Page number
                [],
            ],
            'Search by last name, first item only' => [
                'last',
                10, // Items on page
                1, // Page number
                [
                    [
                        'id' => 'order/1/#100000001',
                        'type' => 'Order',
                        'name' => 'Order #100000001',
                        'description' => 'firstname lastname',
                    ]
                ],
            ],
        ];
    }
}
