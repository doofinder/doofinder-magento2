<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Token that proves an indexation callback comes from Doofinder.
 *
 * The token goes in the callback URL the module gives Doofinder when it creates a search engine,
 * and Doofinder calls that URL as it is. It is an HMAC of the store view id keyed with the Magento
 * encryption key, so it is never stored and nobody without that key can compute it.
 */
class ProcessCallbackToken
{
    /**
     * Query parameter of the callback URL that carries the token
     */
    public const PARAM_NAME = 'token';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Get the callback token of a store view
     *
     * @param int $storeId
     * @return string
     */
    public function generate(int $storeId): string
    {
        return $this->encryptor->hash('doofinder_process_callback:' . $storeId);
    }

    /**
     * Check the token of a callback against the one of its store view, in constant time
     *
     * @param int $storeId
     * @param string $token
     * @return bool
     */
    public function isValid(int $storeId, string $token): bool
    {
        return hash_equals($this->generate($storeId), $token);
    }
}
