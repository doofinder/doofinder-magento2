<?php

declare(strict_types=1);

namespace Doofinder\Feed\Plugin;

use Doofinder\Feed\Helper\SaleLines;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

/**
 * Adds to the cart section of the storefront the lines of the cart as Doofinder indexes their
 * products, which the Doofinder script sends as the cart. The section is reloaded on every change of
 * the cart, both in Luma and in Hyvä.
 */
class CartSectionPlugin
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SaleLines
     */
    private $saleLines;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CartSectionPlugin constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param SaleLines $saleLines
     * @param LoggerInterface $logger
     */
    public function __construct(CheckoutSession $checkoutSession, SaleLines $saleLines, LoggerInterface $logger)
    {
        $this->checkoutSession = $checkoutSession;
        $this->saleLines = $saleLines;
        $this->logger = $logger;
    }

    /**
     * Adds the `doofinder` key to the cart section.
     *
     * An error is only logged, so the cart section the storefront shows still loads.
     *
     * @param Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Cart $subject, array $result): array
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $result['doofinder'] = [
                'currency' => $quote->getQuoteCurrencyCode(),
                'items' => $this->saleLines->fromQuote($quote),
            ];
        } catch (\Throwable $e) {
            $this->logger->error('[Doofinder] Error adding the cart lines: ' . $e->getMessage());
        }

        return $result;
    }
}
