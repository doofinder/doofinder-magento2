<?php

namespace Doofinder\Feed\Plugin\Url;

/**
 * Plugin for \Magento\Framework\Url\RouteParamsResolver
 *
 * Fix for Magento\Store\Url\Plugin\RouteParamsResolver for Magento 2.1.x
 * where '___store' is adding to query params due to incorrect condition
 */
class RouteParamsResolver
{
    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    private $queryParamsResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     */
    public function __construct(
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
    ) {
        $this->queryParamsResolver = $queryParamsResolver;
    }

    /**
     * Process scope query parameters.
     *
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param array $data
     * @param boolean $unsetOldParams
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    public function beforeSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        array $data,
        $unsetOldParams = true
    ) {
        // phpcs:enable
        if (isset($data['doofinder_product_url'])) {
            $params = $this->queryParamsResolver->getQueryParams();
            if (isset($params['___store'])) {
                unset($params['___store']);
                $this->queryParamsResolver->setQueryParams($params);
            }

            unset($data['doofinder_product_url']);
        }

        return [$data, $unsetOldParams];
    }
}
