<?php

namespace Doofinder\Feed\Helper;

use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Exception;

/**
 * Class ComposerVersionProvider
 * The class responsible for getting composer information about the module.
 */
class ComposerVersionProvider
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var array
     */
    private $package;

    /**
     * @var string|null
     */
    private $packageName;

    /**
     * PackageInfo constructor.
     * @param DirectoryList $directoryList
     * @param DriverInterface $driver
     * @param Serializer $serializer
     * @param string|null $packageName
     */
    public function __construct(
        DirectoryList $directoryList,
        DriverInterface $driver,
        Serializer $serializer,
        $packageName = null
    ) {
        $this->directoryList = $directoryList;
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->packageName = $packageName;
    }

    /**
     * @return string
     */
    private function getVendorPath()
    {
        return './vendor';
    }

    /**
     * @return array
     * @throws FileSystemException On missing file.
     */
    private function parseComposerInstalledJson()
    {
        return $this->serializer->unserialize(
            $this->driver->fileGetContents(
                $this->directoryList->getPath(AppDirectoryList::ROOT)
                . DIRECTORY_SEPARATOR
                . $this->getVendorPath()
                . DIRECTORY_SEPARATOR
                . 'composer'
                . DIRECTORY_SEPARATOR
                . 'installed.json'
            )
        );
    }

    /**
     * @return array
     * @throws FileSystemException On missing file.
     */
    private function getPackageInfo()
    {
        if (!$this->package) {
            $packages = $this->parseComposerInstalledJson();
            foreach ($packages as $package) {
                if (array_search($this->packageName, $package) === 'name') {
                    $this->package = $package;
                    break;
                }
            };
        }
        return $this->package;
    }

    /**
     * Gets the composer version ("v1.0.0")
     * @return string|integer
     */
    public function getComposerVersion()
    {
        try {
            return isset($this->getPackageInfo()['version']) ? $this->getPackageInfo()['version'] : 0;
        } catch (Exception $exception) {
            return 0;
        }
    }
}
