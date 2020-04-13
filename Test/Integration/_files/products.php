<?php

// @phpcs:disable

/** @var array defaults */
$defaults = [
    'type_id' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
    'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
    'in_stock' => true,
    'categories' => [5],
];

/** @var array categories in fixture */
$categories = [
    3 => ['is_active' => true, 'path' => '1/2/3'],
    4 => ['is_active' => true, 'path' => '1/2/3/4'],
    5 => ['is_active' => true, 'path' => '1/2/3/4/5'],
    6 => ['is_active' => false, 'path' => '1/2/3/4/6'],
    7 => ['is_active' => false, 'path' => '1/2/7'],
    8 => ['is_active' => true, 'path' => '1/2/7/8'],
    9 => ['is_active' => true, 'path' => '1/2/7/8/9'],
];

/** @var array products in fixture */
$products = [
    1 => [
        'description' => 'Standard product',
    ],
    2 => [
        'description' => 'Product not visible',
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
    ],
    3 => [
        'description' => 'Product visible in search only',
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
    ],
    4 => [
        'description' => 'Product visible in catalog only',
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
    ],
    5 => [
        'description' => 'Product disabled',
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
    ],
    6 => [
        'description' => 'Product out of stock',
        'in_stock' => false,
    ],
    7 => [
        'description' => 'With both active and inactive category',
        'categories' => [4, 6],
    ],
    8 => [
        'description' => 'With category having inactive parent',
        'categories' => [8],
    ],
    9 => [
        'description' => 'With category having inactive ancestor',
        'categories' => [9],
    ],
];

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

/**
 * Create categories
 */
foreach ($categories as $id => $data) {
    $category = $objectManager->create(\Magento\Catalog\Model\Category::class);
    $category->isObjectNew(true);
    $category
        ->setId($id)
        ->setName('Category ' . $id)
        ->setPath($data['path'])
        ->setIsActive($data['is_active'])
        ->save();
}

/**
 * Create products
 */
foreach ($products as $id => $data) {
    $data += $defaults;

    /** @var $product \Magento\Catalog\Model\Product */
    $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setTypeId($data['type_id'])
        ->setId($id)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Product ' . $id)
        ->setSku('product-' . $id)
        ->setPrice(10)
        ->setWeight(1)
        ->setTaxClassId(0)
        ->setDescription($data['description'])
        ->setShortDescription($data['description'])
        ->setVisibility($data['visibility'])
        ->setStatus($data['status'])
        ->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 100,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => $data['in_stock'],
            ]
        );

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
    $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    $productRepository->save($product);

    $categoryLinkManagement->assignProductToCategories(
        $product->getSku(),
        $data['categories']
    );
}
