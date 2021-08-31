<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smartwave\Porto\Model\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\BlockFactory as BlockFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResourceBlock;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\PageFactory as PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResourceBlock;

class Cms
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_storeManager;

    private $_importPath;

    protected $_parser;

    protected $_blockCollectionFactory;

    protected $_blockRepository;

    protected $_blockFactory;

    protected $_pageCollectionFactory;

    protected $_pageRepository;

    protected $_pageFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BlockCollectionFactory $blockCollectionFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        PageCollectionFactory $pageCollectionFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        PageFactory $pageFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_blockFactory = $blockFactory;
        $this->_blockRepository = $blockRepository;
        $this->_pageCollectionFactory = $pageCollectionFactory;
        $this->_pageFactory = $pageFactory;
        $this->_pageRepository = $pageRepository;
        $this->_importPath = BP . '/app/code/Smartwave/Porto/etc/import/';
        $this->_parser = new \Magento\Framework\Xml\Parser();
    }

    public function importCms($type, $demo_version)
    {
        $file_name = $type;
        if ($demo_version == "underware") {
            $file_name = $demo_version;
        }
        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('*********************************************************');
        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($type);
        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($demo_version);
        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($file_name);
        $this->importCmsByFileMame($type, $file_name, $demo_version);
    }


    public function importCmsByFileMame($type, $file_name, $demo_version)
    {
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'import_path' => '',
            'request_success' => false,
            'request_message' => __('Error during Import CMS Sample Datas.'),
        ]);
        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("start import ***********************");
        try {
            $xmlPath = $this->_importPath . $file_name . '.xml';
            $demoCMSxmlPath = $this->_importPath . 'demo_cms.xml';

            $overwrite = false;

            if($this->_scopeConfig->getValue("porto_settings/install/overwrite_".$type)) {
                $overwrite = true;
            }

            if (!is_readable($xmlPath) || !is_readable($demoCMSxmlPath))
            {
                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("Can't get the data file for import cms blocks/pages ".$xmlPath);
                throw new \Exception(
                    __("Can't get the data file for import cms blocks/pages: ".$xmlPath)
                );
            } else {
                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("start import from *************".$xmlPath);
            }
            $data = $this->_parser->load($xmlPath)->xmlToArray();
            $cms_data = $this->_parser->load($demoCMSxmlPath)->xmlToArray();
            ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("load xml success");
            $arr = array();
            if($demo_version != "0") {
                foreach($cms_data['root']['demos'][$demo_version][$type]['item'] as $item) {
                    if(!is_array($item)) {
                        $arr[] = $item; // 元素赋值
                    } else {
                        foreach($item as $__item) {
                            $arr[] = $__item;
                        }
                    }
                }
            }
            $cms_collection = null;
            $conflictingOldItems = array();
            if (array_key_exists('root', $data)) {
                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("root key exist");
                if (array_key_exists($type, $data['root'])) {
                    ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key exist".$type);
                    if (array_key_exists('cms_item', $data['root'][$type])) {
                        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key exist cms_item ");
                        if (is_array($data['root'][$type]['cms_item'])) {
                            ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(" cms_item is array ");
                            foreach ($data['root'][$type]['cms_item'] as $key => $value) {
                                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($key);
                            }
                        }
                        foreach ($data['root'][$type]['cms_item'] as $_item) {
                            if (array_key_exists('identifier', $_item)) {
                                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key exist identifier ");
                            } else {
                                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key not exist identifier ");
                            }
                        }
                    } else {
                        ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key not exist cms_item ");
                    }
                } else {
                    ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("key not exist".$type);
                }
            } else {
                ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("root key not exist");
            }

            $i = 0;
            foreach($data['root'][$type]['cms_item'] as $_item) {
                $exist = false;
                if($demo_version == "0" || in_array($_item['identifier'],$arr)){
                    if($type == "blocks") {
                        $cms_collection = $this->_blockCollectionFactory->create()->addFieldToFilter('identifier', $_item['identifier']);
                        if(count($cms_collection) > 0)
                            $exist = true;

                    }else {
                        $cms_collection = $this->_pageCollectionFactory->create()->addFieldToFilter('identifier', $_item['identifier']);
                        if(count($cms_collection) > 0)
                            $exist = true;

                    }
                    if($overwrite) {
                        if($exist) {
                            $conflictingOldItems[] = $_item['identifier'];
                            if($type == "blocks")
                                $this->_blockRepository->deleteById($_item['identifier']);
                            else
                                $this->_pageRepository->deleteById($_item['identifier']);
                        }
                    } else {
                        if($exist) {
                            $conflictingOldItems[] = $_item['identifier'];
                            continue;
                        }
                    }
                    $_item['stores'] = [0];
                    if($type == "blocks") {
                        $this->_blockFactory->create()->setData($_item)->save();
                    } else {
                        $this->_pageFactory->create()->setData($_item)->save();
                    }
                    $i++;
                }
            }
            $message = "";
            if ($i)
                $message = $i." item(s) was(were) imported.";
            else
                $message = "No items were imported.";
            ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("import complete ".$message);
            $gatewayResponse->setIsValid(true);
            $gatewayResponse->setRequestSuccess(true);

            if ($gatewayResponse->getIsValid()) {
                if ($overwrite){
                    if($conflictingOldItems){
                        $message .= "Items (".count($conflictingOldItems).") with the following identifiers were overwritten:<br/>".implode(', ', $conflictingOldItems);
                    }
                } else {
                    if($conflictingOldItems){
                        $message .= "<br/>Unable to import items (".count($conflictingOldItems).") with the following identifiers (they already exist in the database):<br/>".implode(', ', $conflictingOldItems);
                    }
                }
            }
            $gatewayResponse->setRequestMessage(__($message));
        } catch (\Exception $exception) {
            ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("Can't get the data file for import cms blocks/pages ".($exception->getMessage()));
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestMessage($exception->getMessage());
        }

        return $gatewayResponse;
    }

    function arrayToString($arr) {
        if (is_array($arr)){
            return implode(',', array_map('arrayToString', $arr));
        }
        return $arr;
    }
}
