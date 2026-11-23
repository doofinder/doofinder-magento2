<?php

declare(strict_types=1);

namespace Doofinder\Feed\Observer\Sales;

use Doofinder\Feed\ApiClient\Client;
use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Helper\SaleLines;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Sends to Doofinder the sales of the orders the shopper reaches the order success page with.
 *
 * Magento shows that page once per order (a reload sends the shopper to the cart) and only for the
 * orders placed in the storefront, so the orders created in the admin or through the API are left
 * out. The request is the shopper's, so it brings the Doofinder session the storefront copies to a
 * cookie. Doofinder registers the sale only for the stores it takes the sales from the module for.
 */
class SendSaleObserver implements ObserverInterface
{
    private const COOKIE = 'doofinder_session';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SaleLines
     */
    private $saleLines;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var ManagementClientFactory
     */
    private $managementClientFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Header
     */
    private $httpHeader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SendSaleObserver constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SaleLines $saleLines
     * @param StoreConfig $storeConfig
     * @param ManagementClientFactory $managementClientFactory
     * @param CookieManagerInterface $cookieManager
     * @param Header $httpHeader
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SaleLines $saleLines,
        StoreConfig $storeConfig,
        ManagementClientFactory $managementClientFactory,
        CookieManagerInterface $cookieManager,
        Header $httpHeader,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->saleLines = $saleLines;
        $this->storeConfig = $storeConfig;
        $this->managementClientFactory = $managementClientFactory;
        $this->cookieManager = $cookieManager;
        $this->httpHeader = $httpHeader;
        $this->logger = $logger;
    }

    /**
     * Sends the sale of each order of the event.
     *
     * An error is only logged, as the shopper is loading the order success page.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $session = $this->getSession();
            foreach ((array) $observer->getEvent()->getOrderIds() as $orderId) {
                $this->sendSale($this->orderRepository->get((int) $orderId), $session);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[Doofinder] Error sending the sale: ' . $e->getMessage());
        }
    }

    /**
     * Sends the sale of an order to the search engine of its store view.
     *
     * Only what the sale needs: no customer data.
     *
     * @param Order $order
     * @param array|null $session
     * @return void
     */
    private function sendSale(Order $order, ?array $session)
    {
        $hashId = $this->storeConfig->getHashId((int) $order->getStoreId());
        if (empty($hashId)) {
            return;
        }

        $sale = [
            'order_id' => $order->getIncrementId(),
            'currency' => $order->getOrderCurrencyCode(),
            'user_agent' => $this->httpHeader->getHttpUserAgent(),
            'session' => $session,
            'line_items' => $this->saleLines->fromOrder($order),
        ];

        $this->managementClientFactory->create(['apiType' => Client::DOOPLUGINS])->sendSale($sale, $hashId);
    }

    /**
     * The Doofinder session of the cookie, or null without one.
     *
     * @return array|null
     */
    private function getSession(): ?array
    {
        $cookie = json_decode((string) $this->cookieManager->getCookie(self::COOKIE), true);
        $session = [];
        foreach (['session_id', 'user_id', 'hashid'] as $key) {
            if (isset($cookie[$key])
                && is_string($cookie[$key])
                && preg_match('/^[A-Za-z0-9-]{1,64}$/', $cookie[$key])) {
                $session[$key] = $cookie[$key];
            }
        }

        return isset($session['session_id'], $session['user_id']) ? $session : null;
    }
}
