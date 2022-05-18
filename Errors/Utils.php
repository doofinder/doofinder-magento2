<?php
declare(strict_types=1);

namespace Doofinder\Feed\Errors;

class Utils
{
    /**
     * @param $statusCode
     * @param $response
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public static function handleErrors($statusCode, $response)
    {
        switch ($statusCode) {
            case 403:
                throw new NotAllowed(
                    "The user does not have permissions to perform this operation: " . Utils::readError($response)
                );
            case 401:
                throw new NotAllowed("The user hasn't provided valid authorization: " . Utils::readError($response));
            case 404:
                throw new NotFound("Not Found: " . Utils::readError($response));
            case 409:
                if (preg_match('/indexing.*progress/i', $response) == 1) {
                    // The search engine is locked
                    throw new IndexingInProgress(Utils::readError($response));
                } else {
                    if (preg_match('/type.*already created/i', $response) == 1) {
                        throw new TypeAlreadyExists(Utils::readError($response));
                    } else {
                        // trying to post with an already used id
                        throw new BadRequest("Request conflict: " . Utils::readError($response));
                    }
                }
            case 429:
                if (stripos($response, 'throttled') !== false) {
                    throw new ThrottledResponse(Utils::readError($response));
                } else {
                    throw new QuotaExhausted(
                        "The query quota has been reached. No more queries can be requested right now"
                    );
                }
        }

        if ($statusCode >= 500) {
            throw new WrongResponse("Server error: " . Utils::readError($response));
        }

        if ($statusCode >= 400) {
            throw new BadRequest("The client made a bad request: " . Utils::readError($response));
        }
    }

    private static function readError($response): string
    {
        $error = \Zend_Json::decode($response);
        if ($error === null || !isset($error['error']['message'])) {
            $error = $response;
        } else {
            $error = $error['error']['message'];
        }

        return $error;
    }
}
