<?php
namespace Doofinder\Feed\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

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
        $product = $this->productRepository->getById($this->getRequest()->getParam('id'));
        $qty = $this->getRequest()->getParam('qty');

        $session = $this->checkoutSession->create();
        $quote = $session->getQuote();

        $this->logger->info("Request add item to cart");
        $this->logger->info("Product: ", ["product" => $product]);

        $result = $quote->addProduct($product, $qty);

        if (is_a($result, QuoteItem::class)) {
            $this->cartRepository->save($quote);
            $session->replaceQuote($quote)->unsLastRealOrderId();
        } else {
            $this->logger->info("This product is variable, return the url");
            $product_url = $product->getProductUrl();
            $this->logger->info("Product url: ", ["product_url" => $product_url]);

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['product_url' => $product_url]);
        }
    }
}
