<?php
namespace Doofinder\Feed\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
* Class AddToCart
* @package Doofinder\Feed\Controller\Product
*/
class AddToCart extends Action implements HttpPostActionInterface
{
    /** @var \Magento\Checkout\Model\SessionFactory */
    private $checkoutSession;

    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    private $cartRepository;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepository;

    /** @var \Magento\Framework\Serialize\Serializer\Json */
    private $json;

    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable */
    private $configurableType;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    /**
      * AddToCart constructor.
      * @param Context $context
      * @param \Magento\Framework\Serialize\Serializer\Json $json
      * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
      * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
      * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
      * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
      * @param \Psr\Log\LoggerInterface $logger
      * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
      */
    public function __construct(
        Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->configurableType = $configurableType;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
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
