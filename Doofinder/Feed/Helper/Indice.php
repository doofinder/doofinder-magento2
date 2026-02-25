<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Doofinder\Feed\Errors\NotFound;
use Magento\Framework\App\Helper\AbstractHelper;

class Indice extends AbstractHelper
{

    /**
     * Checks if Magento indice exists in search engine
     *
     * @param array $searchEngine
     * @param string $name
     * @return bool
     * @throws NotFound
     */
    public function checkIndiceExistsInSearchEngine(array $searchEngine, string $name): bool
    {
        $indiceExists = false;
        foreach ($searchEngine['indices'] as $i) {
            if ($i['name'] == $name) {
                $indiceExists = true;
                break;
            }
        }
        return $indiceExists;
    }
}
