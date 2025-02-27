<?php

namespace Doofinder\Feed\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\DataObject;

/**
 * Class AddToCart. This class aims at opening a way for adding to cart from the layer
 */
class AddToCart extends Action implements HttpPostActionInterface
{
    /** @var \Magento\Checkout\Model\SessionFactory */
    private $checkoutSession;

    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    private $cartRepository;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepository;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    /**
     * AddToCart constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $product = $this->productRepository->getById($productId);
        $qty = $this->getRequest()->getParam('qty');
        $session = $this->checkoutSession->create();
        $quote = $session->getQuote();
        $params = [
            'product' => $productId,
            'qty' => $qty
        ];

        $this->logger->info("Request add item to cart");
        $this->logger->info("Product: ", ["product" => $product]);

        if ($product->getTypeId() == ProductType::TYPE_BUNDLE) {
            $bundleOptions = $this->getBundleOptions($product);
            if (!empty($bundleOptions)) {
                $params['bundle_option'] = $bundleOptions;
                $this->logger->info("Bundle options: ", ["bundle_options" => $bundleOptions]);
            }
        }

        $params = new DataObject($params);
        $result = $quote->addProduct($product, $params);
        $resultJson = $this->resultJsonFactory->create();

        if (is_a($result, QuoteItem::class)) {
            //Update totals
            $quote->setIsActive(true);
            $quote->collectTotals()->save();
            $session->replaceQuote($quote)->unsLastRealOrderId();
            $cart = $this->cartRepository->get($quote->getId());
            $this->cartRepository->save($cart);

            return $resultJson->setData(['success' => true]);
        } else {
            $this->logger->info("This product is variable, return the url");
            $product_url = $product->getProductUrl();
            $this->logger->info("Product url: ", ["product_url" => $product_url]);

            return $resultJson->setData(['product_url' => $product_url]);
        }
    }

    /**
     * Get all the selection products used in bundle product
     * @param $product
     * @return mixed
     */
    private function getBundleOptions($product)
    {
        $bundleOptions = [];
        $productType = $product->getTypeInstance();
        $optionsIds = $productType->getOptionsIds($product);
        $selectionCollection = $productType->getSelectionsCollection($optionsIds, $product);

        foreach ($selectionCollection as $selection) {
            $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
        }
        return $bundleOptions;
    }
}
