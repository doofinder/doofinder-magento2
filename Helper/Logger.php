<?php
namespace Doofinder\Feed\Helper;

use Exception;
use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
/**
 * Helper class for logging or information during events
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Logger extends BaseHandler
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
   protected $_dir;


    public function __construct(
        PsrLoggerInterface $logger,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->logger = $logger;
        $this->_dir = $dir;
    }
        
    protected function getMsgType($msg)
    {
        try
        {
            if(isset($msg['exception']))
            {
                return 'ERROR';
            }
            else
            {
                return 'INFORMATION';
            }
        }
        catch(Exception $ex)
        {
            return 'INFORMATION';
        }
        
    }
    /**
     * writeLogs
     *
     * @param  mixed $type
     * @param  mixed $msg
     * @return void
     */
    public function writeLogs($level,$msg)
    {
        //check if this type is set
        if($level == 0)
        {
            //no logging
            return;
        }
        try
        {            
            $folder = $this->_dir->getPath('log');            
            $this->logger = new \Monolog\Logger('doofinder');
            switch ($level)
            {
                case 1:
                    //level 1 just log information
                    $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($folder . '/doofinder/errors.log'));
                    $this->logger->error(json_encode($msg));
                break;

                case 2:
                    //level 2 just log errors only
                    $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($folder . '/doofinder/info.log'));
                    $this->logger->info(json_encode($msg));            
                break;

                case 3:
                    //nested check for logging both informations we want them in separate files
                    //we only have twp states either error or info
                    if($this->getMsgType($msg) == 'ERROR')
                    {
                        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($folder . '/doofinder/errors.log'));
                        $this->logger->error(json_encode($msg));
                    }
                    else
                    {
                        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($folder . '/doofinder/info.log'));
                        $this->logger->info(json_encode($msg));
                    }
                break;

                default:
                    $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($folder . '/doofinder/info.log'));
                    $this->logger->info(json_encode($msg));
                break;

            }
    }
    catch(Exception $ex)
    {
        $this->logger->error($ex->getMessage());    
    }
        
    }
   

    
}
