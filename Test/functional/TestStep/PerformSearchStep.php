<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Doofinder\Feed\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

/**
 * Perform a search step
 *
 * @class PerformSearchStep
 */
class PerformSearchStep implements TestStepInterface
{
    /**
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * @var CatalogSearchQuery
     */
    private $catalogSearch;

    /**
     * Preparing step properties
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogSearchQuery $catalogSearch
     * @param integer|null $queryLength
     */
    public function __construct(
        CmsIndex $cmsIndex,
        CatalogSearchQuery $catalogSearch,
        $queryLength = null
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogSearch = $catalogSearch;
        $this->queryLength = $queryLength;
    }

    /**
     * Run step
     *
     * @return void
     */
    public function run()
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getSearchBlock()->search($this->catalogSearch->getQueryText(), $this->queryLength);
    }
}
