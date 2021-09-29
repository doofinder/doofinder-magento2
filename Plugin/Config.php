<?php

namespace Doofinder\Feed\Plugin;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Config plugin
 */
class Config
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indexer;

    
    /**
     * messageManager
     *
     * @var mixed
     */
    private $messageManager;
    //global url scheme 
    const URL_SCHEME = 'https';
    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     */
    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PsrLoggerInterface $logger
    ) {
        $this->indexer = $indexer;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Store doofinder section config
     *
     * This plugins allows to store doofinder section config
     * right before config update, so Indexer helper is able
     * to check if index needs invalidating.
     *
     * @param  \Magento\Config\Model\Config $config
     * @param  mixed $value
     * @return mixed
     */
    public function beforeSave(\Magento\Config\Model\Config $config, $value = null)
    {
       try
        {
            //replace the object
            $configdata = $config->getData('groups');
            //get urls
            $search_server_url = $configdata['doofinder_account']['fields']['search_server']['value'];
            $managment_server_url = $configdata['doofinder_account']['fields']['management_server']['value'];

            //get protocols of the url
            $search_server_protocol = parse_url($search_server_url, PHP_URL_SCHEME);
            $management_server_protocol = parse_url($managment_server_url, PHP_URL_SCHEME);

            //check if protocol is set
            if (!isset($search_server_protocol)) 
            {
                $configdata['doofinder_account']['fields']['search_server']['value'] = self::URL_SCHEME.'://'.$search_server_url;
             
                $this->messageManager->addSuccessMessage(
                    __('Search server is set to HTTPS')
                );

            }
            else if (strtolower($search_server_protocol) !== self::URL_SCHEME) 
            {
                //check if protocols are in http         
                    //force https 
                    $newsearchurl =  str_replace(strtolower($search_server_protocol).'://', 'https://', $search_server_url);
                   
                    $configdata['doofinder_account']['fields']['search_server']['value'] = $newsearchurl;
                    
                    $this->messageManager->addSuccessMessage(
                        __('Search server protocol was changed from HTTP to HTTPS')
                    );
            }

            if (!isset($management_server_protocol)) {
               
                $configdata['doofinder_account']['fields']['management_server']['value'] = self::URL_SCHEME.'://'.$managment_server_url;

                $this->messageManager->addSuccessMessage(
                    __('Management server is set to HTTPS')
                );
            } 
            else if (strtolower($management_server_protocol) !== self::URL_SCHEME) 
            {
                //force https 
                $newmanagmenturl =  str_replace(strtolower($management_server_protocol).'://', 'https://', $managment_server_url);
                
                $configdata['doofinder_account']['fields']['management_server']['value'] =  $newmanagmenturl;
                
                $this->messageManager->addSuccessMessage(
                    __('Management server protocol was changed from HTTP to HTTPS')
                );

            }

            $config->setData('groups', $configdata);

            $indexer = $this->indexer;
            if ($config->getSection() == $indexer::CONFIG_SECTION_ID) {
                $this->indexer->storeOldConfig();
            }

            return  $value;
        }
        catch(\Exception $ex)
        {
            $this->logger->error($ex->getMessage());         
        }
        finally 
        {
            return  $value;
        }
    }
}
