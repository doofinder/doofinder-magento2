<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\Entity\Type $entityType */
$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('catalog_product');
$data = $entityType->getData();
$entityTypeId = $entityType->getId();

/** @var \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->create(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $attributeSetRepository->get($entityType->getDefaultAttributeSetId());
/*$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$attributeSet->setData(
    [
        'attribute_set_name' => 'test_attribute_set',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 100
    ]
);
$attributeSet->validate();
$attributeSet->save();*/

$attributeData = [
    [
        'attribute_code' => 'foo',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'use_in_search' => 1,
        'frontend_label' => ['foo'],
        'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
        'attribute_group_id' => $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId()),
    ]
];

/** @var \Magento\Eav\Model\Entity\Attribute\Group $attributeGroup */
$attributeGroup = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Group::class);
$attributeGroup->setData(
    [
        'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
        'sort_order' => 30,
        'attribute_group_code' => 'test_attribute_group',
        'default_id' => 0,
    ]
);
$attributeGroup->save();

foreach ($attributeData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->setData($data);
    $attribute->save();
}
