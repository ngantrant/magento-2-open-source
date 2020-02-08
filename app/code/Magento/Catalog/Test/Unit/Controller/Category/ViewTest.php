<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Catalog\Helper\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $update;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $view;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Design|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogDesign;

    /**
     * @var \Magento\Catalog\Controller\Category\View
     */
    protected $action;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\View\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * Set up instances and mock objects
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $this->categoryHelper = $this->createMock(\Magento\Catalog\Helper\Category::class);
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->update = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->layout->expects($this->any())->method('getUpdate')->will($this->returnValue($this->update));

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageConfig->expects($this->any())->method('addBodyClass')->will($this->returnSelf());

        $this->page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->setMethods(['getConfig', 'initLayout', 'addPageLayoutHandles', 'getLayout', 'addUpdate'])
            ->disableOriginalConstructor()->getMock();
        $this->page->expects($this->any())->method('getConfig')->will($this->returnValue($this->pageConfig));
        $this->page->expects($this->any())->method('addPageLayoutHandles')->will($this->returnSelf());
        $this->page->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $this->page->expects($this->any())->method('addUpdate')->willReturnSelf();

        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));

        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->resultFactory->expects($this->any())->method('create')->will($this->returnValue($this->page));

        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->any())->method('getEventManager')->will($this->returnValue($this->eventManager));
        $this->context->expects($this->any())->method('getView')->will($this->returnValue($this->view));
        $this->context->expects($this->any())->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));

        $this->category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->catalogDesign = $this->createMock(\Magento\Catalog\Model\Design::class);

        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultPageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->page));

        $this->action = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Controller\Category\View::class,
            [
                'context' => $this->context,
                'catalogDesign' => $this->catalogDesign,
                'categoryRepository' => $this->categoryRepository,
                'storeManager' => $this->storeManager,
                'resultPageFactory' => $resultPageFactory,
                'categoryHelper' => $this->categoryHelper
            ]
        );
    }

    /**
     * Apply custom layout update is correct
     *
     * @dataProvider getInvocationData
     * @return void
     */
    public function testApplyCustomLayoutUpdate(array $expectedData): void
    {
        $categoryId = 123;
        $pageLayout = 'page_layout';

        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                [Action::PARAM_NAME_URL_ENCODED],
                ['id', false, $categoryId]
            ]
        );

        $this->categoryRepository->expects($this->any())->method('get')->with($categoryId)
            ->will($this->returnValue($this->category));

        $this->categoryHelper->expects($this->once())->method('canShow')->with($this->category)->willReturn(true);

        $settings = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getPageLayout', 'getLayoutUpdates']
        );
        $this->category->expects($this->at(1))
            ->method('hasChildren')
            ->willReturn(true);
        $this->category->expects($this->at(2))
            ->method('hasChildren')
            ->willReturn($expectedData[1][0]['type'] === 'default' ? true : false);
        $this->category->expects($this->once())
            ->method('getDisplayMode')
            ->willReturn($expectedData[2][0]['displaymode']);
        $this->expectationForPageLayoutHandles($expectedData);
        $settings->expects($this->atLeastOnce())->method('getPageLayout')->will($this->returnValue($pageLayout));
        $settings->expects($this->once())->method('getLayoutUpdates')->willReturn(['update1', 'update2']);
        $this->catalogDesign->expects($this->any())->method('getDesignSettings')->will($this->returnValue($settings));

        $this->action->execute();
    }

    /**
     * Expected invocation for Layout Handles
     *
     * @param array $data
     * @return void
     */
    private function expectationForPageLayoutHandles($data): void
    {
        $index = 1;

        foreach ($data as $expectedData) {
            $this->page->expects($this->at($index))
            ->method('addPageLayoutHandles')
            ->with($expectedData[0], $expectedData[1], $expectedData[2]);
            $index++;
        }
    }

    /**
     * Data provider for execute method.
     *
     * @return array
     */
    public function getInvocationData(): array
    {
        return [
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'products'], null, false]
                ]
            ],
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default_without_children'], null, false],
                    [['displaymode' => 'page'], null, false]
                ]
            ],
            [
                'layoutHandles' => [
                    [['type' => 'default'], null, false],
                    [['type' => 'default'], null, false],
                    [['displaymode' => 'poducts_and_page'], null, false]
                ]
            ]
        ];
    }
}
