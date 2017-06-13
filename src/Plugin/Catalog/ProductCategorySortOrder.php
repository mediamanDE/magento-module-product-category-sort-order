<?php
/**
 * @copyright: Copyright © 2017 mediaman GmbH. All rights reserved.
 * @see LICENSE.txt
 */

namespace Mediaman\ProductCategorySortOrder\Plugin\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteriaFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\Request\Http;

/**
 * Class ProductCategorySortOrder
 * @package Mediaman\ProductCategorySortOrder\Plugin\Catalog
 */
class ProductCategorySortOrder
{

    /**
     * @var Http
     */
    private $http;

    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * ProductCategorySortOrder constructor.
     * @param Http $http
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Http $http,
        SearchCriteriaFactory $searchCriteriaFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->http = $http;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }

    /**
     * @param ProductCollection $subject
     * @param $page
     */
    public function beforeSetCurPage(ProductCollection $subject, $page)
    {
        $searchCriteriaData = $this->http->get('searchCriteria');
        if (!$searchCriteriaData) {
            return;
        }

        foreach (array_keys((array)$searchCriteriaData) as $key) {
            $value = &$searchCriteriaData[$key];
            unset($searchCriteriaData[$key]);

            $searchCriteriaData[SimpleDataObjectConverter::camelCaseToSnakeCase($key)] = $value;
        }
        $searchCriteria = $this->searchCriteriaFactory->create(['data' => $searchCriteriaData]);

        $shouldSortByPosition = array_reduce(
            (array)$searchCriteria->getSortOrders(),
            function (bool $res, array $sortOrder) {
                if ($sortOrder['field'] === 'position') {
                    $res = true;
                }
                return $res;
            },
            false
        );
        if (!$shouldSortByPosition) {
            return;
        }

        $categoryId = array_reduce($searchCriteria->getFilterGroups(), function ($res, array $filterGroup) {
            return array_reduce($filterGroup['filters'], function ($res, array $filter) {
                if ($filter['field'] === 'category_id') {
                    $res = $filter['value'];
                }
                return $res;
            });
        });
        if (!$categoryId) {
            return;
        }

        /** @var Category $category */
        $category = $this->categoryRepository->get($categoryId);
        $subject->addCategoryFilter($category);
    }
}
