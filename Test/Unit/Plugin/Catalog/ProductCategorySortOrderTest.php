<?php
/**
 * @copyright: Copyright © 2017 mediaman GmbH. All rights reserved.
 * @see LICENSE.txt
 */

namespace Mediaman\ProductCategorySortOrder\Plugin\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Request\Http;

/**
 * Class ProductCategorySortOrderTest
 * @package Mediaman\ProductCategorySortOrder\Plugin\Catalog
 */
class ProductCategorySortOrderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpMock;

    /**
     * @var Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var SearchCriteriaFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaFactoryMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var ProductCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionMock;

    /**
     * @var ProductCategorySortOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    public function setUp()
    {
        $this->httpMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaFactoryMock = $this->getMockBuilder(SearchCriteriaFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchCriteriaFactoryMock->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->categoryRepositoryMock = $this->getMockBuilder(CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new ProductCategorySortOrder(
            $this->httpMock,
            $this->searchCriteriaFactoryMock,
            $this->categoryRepositoryMock
        );
    }

    /**
     * @test ::beforeSetCurPage
     */
    public function testBeforeSetCurPage()
    {
        $categoryIdMock = 42;
        $searchCriteriaData = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => 'category_id',
                            'value' => $categoryIdMock,
                            'conditionType' => 'eq'
                        ],
                    ],
                ],
            ],
            'sort_orders' => [
                [
                    'field' => 'position',
                    'direction' => SortOrder::SORT_ASC,
                ],
            ],
        ];
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn($searchCriteriaData);

        $this->searchCriteriaFactoryMock->expects(static::once())
            ->method('create')
            ->with([
                'data' => $searchCriteriaData,
            ]);
        $this->searchCriteriaMock->expects(static::once())
            ->method('getSortOrders')
            ->willReturn($searchCriteriaData['sort_orders']);
        $this->searchCriteriaMock->expects(static::once())
            ->method('getFilterGroups')
            ->willReturn($searchCriteriaData['filter_groups']);

        $this->categoryRepositoryMock->expects(static::once())
            ->method('get')
            ->with($categoryIdMock)
            ->willReturn($this->categoryMock);

        $this->productCollectionMock->expects(static::once())
            ->method('addCategoryFilter')
            ->with($this->categoryMock);

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }

    /**
     * @test ::beforeSetCurPage with camelCase keys in search criteria
     */
    public function testBeforeSetCurPageWithCamelCaseSearchCriteria()
    {
        $categoryIdMock = 42;
        $searchCriteriaData = [
            'filterGroups' => [
                [
                    'filters' => [
                        [
                            'field' => 'category_id',
                            'value' => $categoryIdMock,
                            'conditionType' => 'eq'
                        ],
                    ],
                ],
            ],
            'sortOrders' => [
                [
                    'field' => 'position',
                    'direction' => SortOrder::SORT_ASC,
                ],
            ],
        ];
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn($searchCriteriaData);

        $this->searchCriteriaFactoryMock->expects(static::once())
            ->method('create')
            ->with([
                'data' => [
                    'filter_groups' => $searchCriteriaData['filterGroups'],
                    'sort_orders' => $searchCriteriaData['sortOrders'],
                ],
            ]);
        $this->searchCriteriaMock->expects(static::once())
            ->method('getSortOrders')
            ->willReturn($searchCriteriaData['sortOrders']);
        $this->searchCriteriaMock->expects(static::once())
            ->method('getFilterGroups')
            ->willReturn($searchCriteriaData['filterGroups']);

        $this->categoryRepositoryMock->expects(static::once())
            ->method('get')
            ->with($categoryIdMock)
            ->willReturn($this->categoryMock);

        $this->productCollectionMock->expects(static::once())
            ->method('addCategoryFilter')
            ->with($this->categoryMock);

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }

    /**
     * @test ::beforeSetCurPage without searchCriteria being specified
     */
    public function testBeforeSetCurPageWithoutSearchCriteria()
    {
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn(null);

        $this->productCollectionMock->expects(static::never())
            ->method('addCategoryFilter');

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }

    /**
     * @test ::beforeSetCurPage without a sort order being specified
     */
    public function testBeforeSetCurPageWithoutSortOrderSpecified()
    {
        $categoryIdMock = 42;
        $searchCriteriaData = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => 'category_id',
                            'value' => $categoryIdMock,
                            'conditionType' => 'eq'
                        ],
                    ],
                ],
            ]
        ];
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn($searchCriteriaData);

        $this->searchCriteriaMock->expects(static::once())
            ->method('getSortOrders')
            ->willReturn(null);

        $this->productCollectionMock->expects(static::never())
            ->method('addCategoryFilter');

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }

    /**
     * @test ::beforeSetCurPage without a sort order by position
     */
    public function testBeforeSetCurPageWithoutSortOrderByPosition()
    {
        $categoryIdMock = 42;
        $searchCriteriaData = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => 'category_id',
                            'value' => $categoryIdMock,
                            'conditionType' => 'eq'
                        ],
                    ],
                ],
            ],
            'sort_orders' => [
                [
                    'field' => 'entity_id',
                    'direction' => SortOrder::SORT_ASC,
                ],
            ],
        ];
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn($searchCriteriaData);

        $this->searchCriteriaMock->expects(static::once())
            ->method('getSortOrders')
            ->willReturn($searchCriteriaData['sort_orders']);

        $this->productCollectionMock->expects(static::never())
            ->method('addCategoryFilter');

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }

    /**
     * @test ::beforeSetCurPage without category_id filter
     */
    public function testBeforeSetCurPageWithoutCategoryIdFilter()
    {
        $searchCriteriaData = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => 'entity_id',
                            'value' => 1,
                            'conditionType' => 'eq'
                        ],
                    ],
                ],
            ],
            'sort_orders' => [
                [
                    'field' => 'position',
                    'direction' => SortOrder::SORT_ASC,
                ],
            ],
        ];
        $this->httpMock->expects(static::once())
            ->method('get')
            ->with('searchCriteria')
            ->willReturn($searchCriteriaData);

        $this->searchCriteriaMock->expects(static::once())
            ->method('getSortOrders')
            ->willReturn($searchCriteriaData['sort_orders']);
        $this->searchCriteriaMock->expects(static::once())
            ->method('getFilterGroups')
            ->willReturn($searchCriteriaData['filter_groups']);

        $this->productCollectionMock->expects(static::never())
            ->method('addCategoryFilter');

        $this->assertNull($this->subject->beforeSetCurPage($this->productCollectionMock, 1));
    }
}
