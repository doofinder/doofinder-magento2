<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer;

use Doofinder\Feed\Api\ChangedProductRepositoryInterface;
use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\ChangedProduct;
use Doofinder\Feed\Model\ChangedProductFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;

abstract class AbstractChangedProductObserver implements ObserverInterface
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ChangedProductFactory
     */
    private $changedProductFactory;

    /**
     * @var ChangedProductRepositoryInterface
     */
    private $changedProductRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StoreConfig $storeConfig,
        ChangedProductFactory $changedProductFactory,
        ChangedProductRepositoryInterface $changedProductRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        LoggerInterface $logger
    ) {
        $this->storeConfig                  = $storeConfig;
        $this->changedProductFactory        = $changedProductFactory;
        $this->changedProductRepository     = $changedProductRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->logger                       = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->storeConfig->isUpdateOnSave()) {
            try {
                /** @var ProductInterface $product */
                $product = $observer->getEvent()->getProduct();
                $operationType = $this->getOperationType();
                
                if (
                    $product->getUpdatedAt() == $product->getCreatedAt() &&
                    $operationType == ChangedProductInterface::OPERATION_TYPE_UPDATE
                ) {
                    $this->setOperationType(ChangedProductInterface::OPERATION_TYPE_CREATE);
                } else if (
                    $product->getUpdatedAt() != $product->getCreatedAt() && 
                    $operationType == ChangedProductInterface::OPERATION_TYPE_CREATE
                ) {
                    $this->setOperationType(ChangedProductInterface::OPERATION_TYPE_UPDATE);
                }
                
                if (
                    $product->getStore()->getId() == 0 
                    || $this->getOperationType() == ChangedProductInterface::OPERATION_TYPE_DELETE
                ) {

                    foreach ($this->storeConfig->getAllStores() as $store) {
                        $this->registerChangedProductStore($product, (int)$store->getId());
                    } 

                } else {
                    $this->registerChangedProductStore($product, (int)$product->getStore()->getId());
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function registerChangedProductStore(ProductInterface $product, int $storeId){
        $changedProduct = $this->createChangedProduct($product, $storeId);
        if (!$this->checkChangedProductExists($changedProduct)) {
            $this->changedProductRepository->save($changedProduct);
        }
        try {
            $this->changedProductRepository->save($changedProduct);
        } catch (AlreadyExistsException $e) {
            $this->logger->debug(
                sprintf(
                    'Product %s (ID: %s) %s change has been already registered.',
                    $product->getSku(),
                    $product->getId(),
                    $this->getOperationType()
                )
            );
        }
    }

    /**
     * Create changed product
     *
     * @param ProductInterface $product
     * @param int $storeId
     *
     * @return ChangedProduct
     */
    protected function createChangedProduct(ProductInterface $product, int $storeId): ChangedProduct
    {
        $changedProduct = $this->changedProductFactory->create();
        $changedProduct
            ->setProductId((int)$product->getId())
            ->setStoreId($storeId)
            ->setOperationType($this->getOperationType());

        return $changedProduct;
    }

    /**
     * Check if product change type is already registered
     *
     * @param ChangedProductInterface $changedProduct
     * @return bool
     */
    protected function checkChangedProductExists(ChangedProductInterface $changedProduct): bool
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder
            ->addFilter(ChangedProductInterface::PRODUCT_ID, $changedProduct->getProductId())
            ->addFilter(ChangedProductInterface::STORE_ID, $changedProduct->getStoreId())
            ->addFilter(ChangedProductInterface::OPERATION_TYPE, $changedProduct->getOperationType());
        $searchCriteria = $searchCriteriaBuilder->create();
        $changedProductList = $this->changedProductRepository->getList($searchCriteria);

        return (bool)$changedProductList->getTotalCount();
    }

    abstract protected function getOperationType(): string;
    abstract protected function setOperationType(string $operationType);
}
