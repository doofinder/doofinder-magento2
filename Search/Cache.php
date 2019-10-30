<?php

namespace Doofinder\Feed\Search;

/**
 * Class Cache
 * The class responsible for storing raw response from Doofinder
 */
class Cache
{
    /**
     * @var array
     */
    private $response;

    /**
     * @param array $response
     * @return void
     */
    public function setResponse(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
