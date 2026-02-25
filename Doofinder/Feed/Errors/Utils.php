<?php
declare(strict_types=1);

namespace Doofinder\Feed\Errors;

class Utils
{
    /**
     * Handle errors by HTTP status code
     *
     * @param int $statusCode
     * @param string $response
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     */
    public function handleErrors($statusCode, $response)
    {
        switch ($statusCode) {
            case 403:
                throw new NotAllowed(
                    "The user does not have permissions to perform this operation: " . $this->readError($response)
                );
            case 401:
                throw new NotAllowed("The user hasn't provided valid authorization: " . $this->readError($response));
            case 404:
                throw new NotFound("Not Found: " . $this->readError($response));
            case 409:
                if (preg_match('/indexing.*progress/i', $response) == 1) {
                    // The search engine is locked
                    throw new IndexingInProgress($this->readError($response));
                } else {
                    if (preg_match('/type.*already created/i', $response) == 1) {
                        throw new TypeAlreadyExists($this->readError($response));
                    } else {
                        // trying to post with an already used id
                        throw new BadRequest("Request conflict: " . $this->readError($response));
                    }
                }
            case 429:
                if (stripos($response, 'throttled') !== false) {
                    throw new ThrottledResponse($this->readError($response));
                } else {
                    throw new QuotaExhausted(
                        "The query quota has been reached. No more queries can be requested right now"
                    );
                }
        }

        if ($statusCode >= 500) {
            throw new WrongResponse("Server error: " . $this->readError($response));
        }

        if ($statusCode >= 400) {
            throw new BadRequest("The client made a bad request: " . $this->readError($response));
        }
    }

    /**
     * Read the error message from the response for later processing
     *
     * @param string $response
     * @return string
     */
    private function readError($response): string
    {
        $error = json_decode($response, true);
        if ($error === null || !isset($error['error']['message'])) {
            $error = $response;
        } else {
            $error = $error['error']['message'];
        }

        return $error;
    }
}
