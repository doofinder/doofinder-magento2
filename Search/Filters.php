<?php

namespace Doofinder\Feed\Search;

use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceNameResolver;
use Magento\Framework\Search\RequestInterface;

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
     * Filters constructor.
     * @param PriceNameResolver $priceNameResolver
     */
    public function __construct(PriceNameResolver $priceNameResolver)
    {
        $this->priceNameResolver = $priceNameResolver;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function get(RequestInterface $request)
    {
        $filters = ['filter' => [], 'sort' => []];
        $must = $request->getQuery()->getMust();

        foreach ($must as $filter) {
            $ref = $filter->getReference();

            if ($ref->getField() == 'price') {
                $fieldName = $this->priceNameResolver->getFiledName();
                $filters['filter'][$fieldName] = $this->getPriceFilter($ref);
                continue;
            }

            $filters['filter'][$ref->getField()] = [$ref->getValue()];
        }

        if (!method_exists($request, 'getSort')) {
            return $filters;
        }
        foreach ($request->getSort() as $sort) {
            if ($sort['field'] == 'price') {
                $filters['sort'][] = [$this->priceNameResolver->getFiledName() => $sort['direction']];
                continue;
            }
            $filters['sort'][] = [$sort['field'] => $sort['direction']];
        }

        return $filters;
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
