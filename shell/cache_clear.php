<?php
/**
 *
 * User: rthacker
 * Date: 1/24/13
 * Time: 5:17 PM
 * @category    Lyonscg
 * @package     Lyonscg_Shell_CleanCache
 * @copyright   Copyright (c) 2013 Lyons Consulting Group http://www.lyonscg.com
 */

require_once 'abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Lyonscg
 * @package     Lyonscg_
 * @author      Rand Thacker (rthacker@lyonscg.com)
 */
class Mage_Shell_Compiler extends Mage_Shell_Abstract
{
    protected $cache_instance;
    protected $isEnterprise;

    /**
     * Cleans image cache using catalog/product_image model.
     *
     */
    protected function cleanImageCache()
    {
        try {
            echo "Clearing image cache... ";
            flush();
            echo Mage::getModel('catalog/product_image')->clearCache();
            echo "[OK]" . PHP_EOL . PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }


    /**
     * Clears the JS and CSS merged files
     *
     */
    protected function cleanMergedJSCSS()
    {
        try {
            echo "Clearing merged JS/CSS cache... ";
            flush();
            Mage::getModel('core/design_package')->cleanMergedJsCss();
            Mage::dispatchEvent('clean_media_cache_after');
            echo "[OK]" . PHP_EOL . PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }


    /**
     * Parse string with caches and return array of cache types
     *
     * @param string $string
     * @return array
     */
    protected function _parseCacheString($string)
    {
        $types = array();
        $typeCollection = $this->cache_instance->getTypes();

        if ($string == 'all') {
            foreach ($typeCollection as $type) {
                $types[] = $type;
            }
        } else if (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $type) {
                $cacheDef = $typeCollection[$type];
                if (!$cacheDef) {
                    echo 'Warning: Unknown cache with code ' . trim($type) . PHP_EOL;
                } else {
                    $types[] = $cacheDef;
                }
            }
        }
        return $types;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $isEnterprise = class_exists('Enterprise_PageCache_Model_Cache');
        $this->cache_instance = Mage::app()->getCacheInstance();
        if ($this->getArg('dump')) {
            if ($this->getArg('dump'))
                $types = $this->_parseCacheString($this->getArg('dump'));
            else
                $types = $this->_parseCacheString('all');

            foreach ($types as $type) {
                print_r($type);
                echo $type->id . " (as class type=" . get_class($type) . ")" . PHP_EOL;
            }
            die("All done!" . PHP_EOL);
        } else if ($this->getArg('info')) {
            $types = $this->_parseCacheString('all');
            foreach ($types as $type) {
                echo sprintf('%-15s', $type->getId());
                echo sprintf('%-30s', $type->getCacheType());
                echo $type->getDescription() . "\n";
            }
        } else if ($this->getArg('flush') || $this->getArg('flushall')) {
            if ($this->getArg('flush'))
                $types = $this->_parseCacheString($this->getArg('flush'));
            else
                $types = $this->_parseCacheString('all');

            foreach ($types as $type) {
                try {
                    if ($isEnterprise && $type->getId() == 'full_page')
                        Enterprise_PageCache_Model_Cache::getCacheInstance()->cleanType('full_page');
                    else
                        $this->cache_instance->cleanType($type->getId());

                    echo $type->getCacheType() . " (" . $type->getId() . ") cache was flushed successfully" . PHP_EOL;
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                } catch (Exception $e) {
                    echo $type->getIndexer()->getName() . " index type unknown error:" . PHP_EOL;
                    echo $e . PHP_EOL;
                }
            }

        } else if ($this->getArg('flushmagento')) {
            Mage::app()->cleanCache();
            echo "The Magento Cache has been flushed." . PHP_EOL;
        } else if ($this->getArg('flushcache')) {
            Mage::app()->getCacheInstance()->flush();
            echo "The Cache Storage has been flushed." . PHP_EOL;
        } else if ($this->getArg('flushjscss')) {
            Mage::getModel('core/design_package')->cleanMergedJsCss();
            Mage::dispatchEvent('clean_media_cache_after');
            echo "The JavaScript/CSS Cache has been flushed." . PHP_EOL;
        } else if ($this->getArg('flushimages')) {
            Mage::getModel('catalog/product_image')->clearCache();
            Mage::dispatchEvent('clean_catalog_images_cache_after');
            echo "The Catalog Images Cache has been flushed." . PHP_EOL;
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cache_clear.php -- [options]

  --flush <cache>               Flush cache(s)
  info                          Show allowed caches
  flushall                      Flush all caches individually (as listed from info)
  flushmagento                  Flush magento (Flush Magento Cache orange button)
  flushcache                    Flush cache (Flush Cache Storage orange button)
  flushjscss                    Flush JS CSS (Flush JavaScript/CSS Cache orange button)
  flushimages                   Flush Catalog Product images (Flush Catalog Images Cache orange button)
  help                          This help

  <cache>     Comma separated cache codes or value "all" for all caches

USAGE;
    }
}

$shell = new Mage_Shell_Compiler();
$shell->run();
