<?php

namespace Smart\AlgoliaSearch\Helper;

use Magento;
use Magento\Directory\Model\Currency as DirCurrency;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Locale\Currency;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;

/**
 * Class ConfigHelper
 *
 * @package Smart\AlgoliaSearch\Helper
 */
class ConfigHelper extends \Algolia\AlgoliaSearch\Helper\ConfigHelper
{
    const XML_PATH_IMAGE_WIDTH = 'algoliasearch_images/image/width';
    const XML_PATH_IMAGE_HEIGHT = 'algoliasearch_images/image/height';
    const XML_PATH_IMAGE_TYPE = 'algoliasearch_images/image/type';

    private $configInterface;
    private $objectManager;
    private $currency;
    private $storeManager;
    private $dirCurrency;
    private $directoryList;
    private $moduleResource;
    private $productMetadata;
    private $eventManager;
    protected $_coreRegistry;
    protected $categoryModel;
    protected $eavConfig;

    public function __construct(
        Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        Magento\Framework\ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Currency $currency,
        DirCurrency $dirCurrency,
        DirectoryList $directoryList,
        Magento\Framework\Module\ResourceInterface $moduleResource,
        Magento\Framework\App\ProductMetadataInterface $productMetadata,
        Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $_coreRegistry,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        parent::__construct($configInterface, $objectManager, $storeManager, $currency, $dirCurrency, $directoryList, $moduleResource,
            $productMetadata, $eventManager);
        $this->objectManager = $objectManager;
        $this->configInterface = $configInterface;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->dirCurrency = $dirCurrency;
        $this->directoryList = $directoryList;
        $this->moduleResource = $moduleResource;
        $this->productMetadata = $productMetadata;
        $this->eventManager = $eventManager;
        $this->_coreRegistry = $_coreRegistry;
        $this->categoryModel = $categoryModel;
        $this->eavConfig = $eavConfig;
    }

    public function getImageWidth($storeId = null)
    {
        $imageWidth = $this->configInterface->getValue(self::XML_PATH_IMAGE_WIDTH, ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($imageWidth)) {
            return;
        }

        return $imageWidth;
    }

    public function getImageHeight($storeId = null)
    {
        $imageHeight = $this->configInterface->getValue(self::XML_PATH_IMAGE_HEIGHT, ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($imageHeight)) {
            return;
        }

        return $imageHeight;
    }

    public function getImageType($storeId = null)
    {
        return $this->configInterface->getValue(self::XML_PATH_IMAGE_TYPE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $group_id
     * @return array
     * Retrieve default original formated price
     */
    public function getAttributesToRetrieve($group_id)
    {
        if (false === $this->isCustomerGroupsEnabled()) {
            return [];
        }

        $attributes = [];
        foreach ($this->getProductAdditionalAttributes() as $attribute) {
            if ($attribute['attribute'] !== 'price') {
                $attributes[] = $attribute['attribute'];
            }
        }

        $attributes = array_merge($attributes, [
            'objectID',
            'name',
            'url',
            'visibility_search',
            'visibility_catalog',
            'categories',
            'categories_without_path',
            'thumbnail_url',
            'image_url',
            'in_stock',
            'type_id',
            'value',
        ]);

        $currencies = $this->dirCurrency->getConfigAllowCurrencies();

        foreach ($currencies as $currency) {
            $attributes[] = 'price.' . $currency . '.default';
            $attributes[] = 'price.' . $currency . '.default_formated';
            $attributes[] = 'price.' . $currency . '.default_original_formated';
            $attributes[] = 'price.' . $currency . '.group_' . $group_id;
            $attributes[] = 'price.' . $currency . '.group_' . $group_id . '_formated';
            $attributes[] = 'price.' . $currency . '.special_from_date';
            $attributes[] = 'price.' . $currency . '.special_to_date';
        }

        $transport = new DataObject($attributes);
        $this->eventManager->dispatch('algolia_get_retrievable_attributes', ['attributes' => $transport]);
        $attributes = $transport->getData();

        return ['attributesToRetrieve' => $attributes];
    }

    /**
     * @param null $storeId
     * @return bool
     * Return bool value from isAddToCartEnabled config
     */
    public function isAddToCartEnable($storeId = null)
    {
        return (bool) $this->configInterface->getValue(self::XML_ADD_TO_CART_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * @return mixed
     */
    public function getPlaceholderImage()
    {
        $image_placeholder = $this->configInterface->getValue('catalog/placeholder/image_placeholder');

        return $image_placeholder;
    }

}
