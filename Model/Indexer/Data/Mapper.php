<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data;

use Doofinder\Feed\Api\Data\MapInterface;

class Mapper
{
    /**
     * @var MapInterface
     */
    private $maps;

    /**
     * Mapper constructor.
     * @param MapInterface[] $maps
     */
    public function __construct(array $maps = [])
    {
        $this->maps = $maps;
    }

    /**
     * @param string $type
     * @return MapInterface
     * @throws \DomainException If map does not exist.
     * @throws \RuntimeException If map does not implement interface.
     */
    public function get($type)
    {
        if (!isset($this->maps[$type])) {
            throw new \DomainException('Map does not exist');
        }
        $map = $this->maps[$type];
        if (!$map instanceof MapInterface) {
            throw new \RuntimeException(
                sprintf(
                    '%s does not implement %s',
                    $type,
                    MapInterface::class
                )
            );
        }

        return $map;
    }
}
