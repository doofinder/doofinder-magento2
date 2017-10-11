<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Doofinder\Feed\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test for Doofinder search
 *
 * 1. Setup Doofinder search engine
 * 2. Run catalogsearch reindex
 * 3. Perform search
 *
 * @class SearchTest
 */
class SearchTest extends Scenario
{
    /**
     * Run scenario
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
