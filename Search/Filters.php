<?php

namespace Doofinder\Feed\Search;

use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameResolver;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\CategoryPosition as CategoryPositionNameResolver;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Class Filters
 * The class responsible for translating Magento filters for Doofinder engine
 */
class Filters
{
    /**
     * @var PriceNameResolver
     */
    private $priceNameResolver;

    /**
     * @var CategoryPositionNameResolver
     */
    private $catPosNameResolver;

    /**
     * Filters constructor.
     * @param PriceNameResolver $priceNameResolver
     * @param CategoryPositionNameResolver $catPosNameResolver
     */
    public function __construct(
        PriceNameResolver $priceNameResolver,
        CategoryPositionNameResolver $catPosNameResolver
    ) {
        $this->priceNameResolver = $priceNameResolver;
        $this->catPosNameResolver = $catPosNameResolver;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function get(RequestInterface $request)
    {
        $filters = ['filter' => [], 'sort' => []];
        $must = $request->getQuery()->getMust();
        $categoryId = null;

        foreach ($must as $filter) {
            $ref = $filter->getReference();

            if ($ref->getField() == 'price') {
                $fieldName = $this->priceNameResolver->getFiledName();
                $filters['filter'][$fieldName] = $this->getPriceFilter($ref);
                continue;
            }
            if ($ref->getField() == 'category_ids') {
                $categoryId = $ref->getValue();
            }

            $value = $this->getFilterValue($ref->getValue());
            $filters['filter'][$ref->getField()] = [$value];
        }

        if (!method_exists($request, 'getSort')) {
            return $filters;
        }
        foreach ($request->getSort() as $sort) {
            $direction = $sort['direction'];
            if ($direction instanceof SortOrder) {
                $direction = $direction->getDirection();
            }

            $fieldName = $sort['field'];
            if ($fieldName == 'price') {
                $fieldName = $this->priceNameResolver->getFiledName();
            } elseif ($fieldName == 'position' && $categoryId) {
                $fieldName = $this->catPosNameResolver->getFiledName($categoryId);
            } elseif ($fieldName == 'relevance') {
                $fieldName = '_score';
            }
            $filters['sort'][] = [$fieldName => $direction];
        }

        return $filters;
    }

    /**
     * Clear value from nested arrays
     *
     * @param mixed $value
     * @return mixed
     */
    private function getFilterValue($value)
    {
        if (is_array($value) && count($value) === 1) {
            $value = reset($value);
            return $this->getFilterValue($value);
        }
        return $value;
    }

    /**
     * @param mixed $ref
     * @return array
     */
    private function getPriceFilter($ref)
    {
        $filter = [];
        if ($ref->getFrom()) {
            $filter['from'] = $ref->getFrom();
        }
        if ($ref->getTo()) {
            $filter['to'] = $ref->getTo();
        }
        return $filter;
    }
}
