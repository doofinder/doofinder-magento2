<?php

namespace Doofinder\Feed\Helper;

use Exception;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

/**
 * Helper class for logging or information during events
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Logger extends MonologLogger
{
    /**
     * logger
     *
     * @var mixed
     */
    public $logger;

    /**
     * _dir
     *
     * @var mixed
     */
    protected $dir;


    /**
     * @param DirectoryList $_dir
     */
    public function __construct(DirectoryList $_dir)
    {
        // $this->logger = $_logger;
        $this->dir = $_dir;
        parent::__construct("doofinder");
    }

    /**
     * writeLogs
     *
     * @param mixed $level
     * @param mixed $msg
     * @return void
     */
    public function writeLogs($level, $msg)
    {
        //set instance with our name
        //check if this type is set
        if ($level === 0) {
            //no logging
            return;
        }
        try {
            $folder = $this->dir->getPath('log');
            switch ($level) {
                case 1:
                    //level 1 just log information
                    $this->pushHandler(new StreamHandler($folder . '/doofinder/errors.log'));
                    $this->error(json_encode($msg));
                    break;
                case 2:
                    //level 2 just log errors only
                    $this->pushHandler(new StreamHandler($folder . '/doofinder/info.log'));
                    $this->info(json_encode($msg));
                    break;
                case 3:
                    //nested check for logging both informations we want them in separate files
                    //we only have two states either error or info
                    if ($this->getMsgType($msg) === 'ERROR') {
                        $this->pushHandler(new StreamHandler($folder . '/doofinder/errors.log'));
                        $this->error(json_encode($msg));
                    } else {
                        $this->pushHandler(new StreamHandler($folder . '/doofinder/info.log'));
                        $this->info(json_encode($msg));
                    }
                    break;
                default:
                    $this->pushHandler(new StreamHandler($folder . '/doofinder/info.log'));
                    $this->info(json_encode($msg));
                    break;

            }
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }

    }

    protected function getMsgType($msg)
    {
        try {
            if (isset($msg['exception'])) {
                return 'ERROR';
            } else {
                return 'INFORMATION';
            }
        } catch (Exception $ex) {
            return 'INFORMATION';
        }

    }


}
