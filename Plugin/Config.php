<?php

namespace Doofinder\Feed\Plugin;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Config plugin
 */
class Config
{
    const URL_SCHEME = 'https';
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indexer;
    //global url scheme
    /**
     * messageManager
     *
     * @var mixed
     */
    private $messageManager;

    /**
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        \Doofinder\Feed\Helper\Indexer              $indexer,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PsrLoggerInterface                          $logger
    )
    {
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
     * @param \Magento\Config\Model\Config $config
     * @param mixed $value
     * @return mixed
     */
    public function beforeSave(\Magento\Config\Model\Config $config, $value = null)
    {
        try {
            //get the groups to a variable
            $configdata = $config->getData('groups');
            if (isset($configdata['doofinder_account'])) {
                //get urls from the
                $search_server_url = $configdata['doofinder_account']['fields']['search_server']['value'];
                $management_server_url = $configdata['doofinder_account']['fields']['management_server']['value'];

                //validate the urls
                if (!filter_var($search_server_url, FILTER_VALIDATE_URL)) {
                    $this->messageManager->addErrorMessage(
                        __('Search server is invalid URL')
                    );
                } else {
                    //get protocols of the url
                    $search_server_protocol = parse_url($search_server_url, PHP_URL_SCHEME);
                    //check if protocol is set
                    if (!isset($search_server_protocol)) {
                        $configdata['doofinder_account']['fields']['search_server']['value'] = self::URL_SCHEME . '://' . $search_server_url;

                        $this->messageManager->addSuccessMessage(
                            __('Search server is set to HTTPS')
                        );

                    } else if (strtolower($search_server_protocol) !== self::URL_SCHEME) {
                        //check if protocols are in http
                        //force https
                        $newsearchurl = str_replace(strtolower($search_server_protocol) . '://', 'https://', $search_server_url);

                        $configdata['doofinder_account']['fields']['search_server']['value'] = $newsearchurl;

                        $this->messageManager->addSuccessMessage(
                            __('Search server protocol was changed from HTTP to HTTPS')
                        );
                    }

                }
                //validate management server url
                if (!filter_var($management_server_url, FILTER_VALIDATE_URL)) {
                    $this->messageManager->addErrorMessage(
                        __('Management server is invalid URL')
                    );
                } else {

                    //check protocol
                    $management_server_protocol = parse_url($management_server_url, PHP_URL_SCHEME);
                    if (!isset($management_server_protocol)) {

                        $configdata['doofinder_account']['fields']['management_server']['value'] = self::URL_SCHEME . '://' . $managment_server_url;

                        $this->messageManager->addSuccessMessage(
                            __('Management server is set to HTTPS')
                        );

                    } else if (strtolower($management_server_protocol) !== self::URL_SCHEME) {
                        //force https
                        $newmanagmenturl = str_replace(strtolower($management_server_protocol) . '://', 'https://', $managment_server_url);

                        $configdata['doofinder_account']['fields']['management_server']['value'] = $newmanagmenturl;

                        $this->messageManager->addSuccessMessage(
                            __('Management server protocol was changed from HTTP to HTTPS')
                        );

                    }
                }

                $config->setData('groups', $configdata);
                $indexer = $this->indexer;
                if ($config->getSection() == $indexer::CONFIG_SECTION_ID) {
                    $this->indexer->storeOldConfig();
                }

                return $value;
            }

        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
        } finally {
            return $value;
        }
    }


}
