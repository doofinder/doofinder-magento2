<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Doofinder\Feed\Serializer;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class for serializing data first to json string and then to base64 string.
 *
 * May be used for cases when json encoding results with a string,
 * which contains characters, which are unacceptable by client.
 */
class Base64GzJson extends Json
{
    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        return base64_encode(gzcompress(parent::serialize($data)));
    }

    /**
     * Unserialize the given string with base64 and json.
     * Falls back to the json-only decoding on failure.
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    public function unserialize($string)
    {
    public function unserialize($string)
    {
        $decoded = base64_decode($string, true);
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return parent::unserialize($string);
        }

        $uncompressed = @gzuncompress($decoded);
        if ($uncompressed === false) {
            return parent::unserialize($string);
        }

        return parent::unserialize($uncompressed);
    }
}
