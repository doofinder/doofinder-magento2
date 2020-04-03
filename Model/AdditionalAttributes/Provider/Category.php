<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Category as CategoryFieldNameResolver;

/**
 * Class Category
 * The class responsible for providing Category attributes code
 */
class Category implements AttributesProviderInterface
{
    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        return [CategoryFieldNameResolver::ATTR_NAME];
    }
}
