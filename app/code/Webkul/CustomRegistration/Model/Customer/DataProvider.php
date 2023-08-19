<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomRegistration\Model\Customer;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Customer\Model\FileUploaderDataResolver;

/**
 * @api
 * @since 100.0.2
 */
class DataProvider extends \Magento\Customer\Model\Customer\DataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    public const MAX_FILE_SIZE = 2097152;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsiteSource;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var array
     */
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var SessionManagerInterface
     * @since 100.1.0
     */
    protected $session;

    /**
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

    /**
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * @var array
     */
    private $forbiddenCustomerFields = [
        'password_hash',
        'rp_token',
        'confirmation',
    ];

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var \Webkul\CustomRegistration\Helper\Order $regHelper
     */
    protected $_regHelper;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

   /**
    * @param string $name
    * @param string $primaryFieldName
    * @param string $requestFieldName
    * @param EavValidationRules $eavValidationRules
    * @param CustomerCollectionFactory $customerCollectionFactory
    * @param Config $eavConfig
    * @param FilterPool $filterPool
    * @param \Magento\Customer\Model\Config\Share $shareConfig
    * @param \Webkul\CustomRegistration\Helper\Order $regHelper
    * @param CountryWithWebsites $countryWithWebsiteSource
    * @param FileProcessorFactory $fileProcessorFactory
    * @param array $meta
    * @param array $data
    * @param ContextInterface $context
    * @param bool $allowToShowHiddenAttributes
    * @param FileUploaderDataResolver|null $fileUploaderDataResolver
    */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EavValidationRules $eavValidationRules,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        FilterPool $filterPool,
        \Magento\Customer\Model\Config\Share $shareConfig,
        \Webkul\CustomRegistration\Helper\Order $regHelper,
        CountryWithWebsites $countryWithWebsiteSource,
        FileProcessorFactory $fileProcessorFactory = null,
        array $meta = [],
        array $data = [],
        ContextInterface $context = null,
        $allowToShowHiddenAttributes = true,
        FileUploaderDataResolver $fileUploaderDataResolver = null
    ) {

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $eavValidationRules,
            $customerCollectionFactory,
            $eavConfig,
            $filterPool,
            $fileProcessorFactory,
            $meta,
            $data,
            $context,
            $allowToShowHiddenAttributes
        );

        $this->eavValidationRules = $eavValidationRules;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->eavConfig = $eavConfig;
        $this->filterPool = $filterPool;
        $this->shareConfig = $shareConfig;
        $this->_regHelper = $regHelper;
        $this->countryWithWebsiteSource = $countryWithWebsiteSource;
        $this->context = $context;
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer')
        );
        $this->meta['address']['children'] = $this->getAttributesMeta(
            $this->eavConfig->getEntityType('customer_address')
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $collectionItems = $this->collection->getItems();
        /** @var Customer $customer */
        foreach ($collectionItems as $customer) {
            $resultData['customer'] = $customer->getData();

            $this->fileUploaderDataResolver->overrideFileUploaderData($customer, $resultData['customer']);

            $resultData['customer'] = array_diff_key(
                $resultData['customer'],
                array_flip($this->forbiddenCustomerFields)
            );
            unset($resultData['address']);

            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $addressId = $address->getId();
                $address->load($addressId);
                $resultData['address'][$addressId] = $address->getData();
                $this->prepareAddressData($addressId, $resultData['address'], $resultData['customer']);

                $this->fileUploaderDataResolver->overrideFileUploaderData($address, $resultData['address'][$addressId]);
            }
            $this->loadedData[$customer->getId()] = $resultData;
        }

        $data = $this->getSession()->getCustomerFormData();
        if (!empty($data)) {
            $customerId = isset($data['customer']['entity_id']) ? $data['customer']['entity_id'] : null;
            $this->loadedData[$customerId] = $data;
            $this->getSession()->unsCustomerFormData();
        }

        return $this->loadedData;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        $customCollection = $this->_regHelper->attributeCollectionFilter();
        $attributeCodeArray = [];
        foreach ($customCollection as $model) {
            $attributeCodeArray[] = $model->getAttributeCode();
        }
        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), $attributeCodeArray)) {
                $this->processFrontendInput($attribute, $meta);
                $attributeCode = $attribute->getAttributeCode();
                // use getDataUsingMethod, since some getters are defined and apply additional
                // processing of returning value
                foreach ($this->metaProperties as $metaName => $origName) {
                    $value = $attribute->getDataUsingMethod($origName);
                    $label = ($metaName === 'label') ? __($value) : $value;
                    $meta[$attributeCode]['arguments']['data']['config'][$metaName] = $label;
                    if ('frontend_input' === $origName) {
                        $meta[$attributeCode]['arguments']['data']['config']['formElement'] =
                            isset($this->formElement[$value])
                            ? $this->formElement[$value]
                            : $value;
                    }
                }

                if ($attribute->usesSource()) {
                    if ($attributeCode == AddressInterface::COUNTRY_ID) {
                        $meta[$attributeCode]['arguments']['data']['config']['options'] =
                            $this->getCountryWithWebsiteSource()->getAllOptions();
                    } else {
                        $meta[$attributeCode]['arguments']['data']['config']['options'] = $attribute->getSource()
                            ->getAllOptions();
                    }
                }
                $rules = $this->eavValidationRules->build(
                    $attribute,
                    $meta[$attributeCode]['arguments']['data']['config']
                );
                if (!empty($rules)) {
                    $meta[$attributeCode]['arguments']['data']['config']['validation'] = $rules;
                }
                $meta[$attributeCode]['arguments']['data']['config']['componentType'] = Field::NAME;
                $meta[$attributeCode]['arguments']['data']['config']['visible'] = $this->canShowAttribute($attribute);

                $this->overrideFileUploaderMetadata(
                    $entityType,
                    $attribute,
                    $meta[$attributeCode]['arguments']['data']['config']
                );
            }
        }
        $this->processWebsiteMeta($meta);
        return $meta;
    }

    /**
     * Check whether the specific attribute can be shown in form: customer registration, customer edit, etc...
     *
     * @param Attribute $customerAttribute
     * @return bool
     */
    private function canShowAttributeInForm(AbstractAttribute $customerAttribute)
    {
        $isRegistration = $this->context->getRequestParam($this->getRequestFieldName()) === null;

        if ($customerAttribute->getEntityType()->getEntityTypeCode() === 'customer') {
            return is_array($customerAttribute->getUsedInForms()) &&
                (
                    (in_array('customer_account_create', $customerAttribute->getUsedInForms()) && $isRegistration) ||
                    (in_array('customer_account_edit', $customerAttribute->getUsedInForms()) && !$isRegistration)
                );
        } else {
            return is_array($customerAttribute->getUsedInForms()) &&
                in_array('customer_address_edit', $customerAttribute->getUsedInForms());
        }
    }

    /**
     * Detect can we show attribute on specific form or not
     *
     * @param Attribute $customerAttribute
     * @return bool
     */
    private function canShowAttribute(AbstractAttribute $customerAttribute)
    {
        $userDefined = (bool) $customerAttribute->getIsUserDefined();
        if (!$userDefined) {
            return $customerAttribute->getIsVisible();
        }

        $canShowOnForm = $this->canShowAttributeInForm($customerAttribute);

        return ($this->allowToShowHiddenAttributes && $canShowOnForm) ||
            (!$this->allowToShowHiddenAttributes && $canShowOnForm && $customerAttribute->getIsVisible());
    }

    /**
     * Get Country With Websites Source
     *
     * @return CountryWithWebsites
     */
    private function getCountryWithWebsiteSource()
    {
        return $this->countryWithWebsiteSource;
    }

    /**
     * Get Customer Config Share
     *
     * @return \Magento\Customer\Model\Config\Share
     */
    private function getShareConfig()
    {
        return $this->shareConfig;
    }

    /**
     * Append global scope parameter and filter options to website meta
     *
     * @param array $meta
     * @return void
     */
    private function processWebsiteMeta(&$meta)
    {
        if (isset($meta[CustomerInterface::WEBSITE_ID]) && $this->getShareConfig()->isGlobalScope()) {
            $meta[CustomerInterface::WEBSITE_ID]['arguments']['data']['config']['isGlobalScope'] = 1;
        }

        if (isset($meta[AddressInterface::COUNTRY_ID]) && !$this->getShareConfig()->isGlobalScope()) {
            $meta[AddressInterface::COUNTRY_ID]['arguments']['data']['config']['filterBy'] = [
                'target' => '${ $.provider }:data.customer.website_id',
                'field' => 'website_ids'
            ];
        }
    }

    /**
     * Overrides file uploader UI component metadata
     *
     * Overrides metadata for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Type $entityType
     * @param AbstractAttribute $attribute
     * @param array $config
     * @return void
     */
    private function overrideFileUploaderMetadata(
        Type $entityType,
        AbstractAttribute $attribute,
        array &$config
    ) {
        if (in_array($attribute->getFrontendInput(), $this->fileUploaderTypes)) {
            $maxFileSize = self::MAX_FILE_SIZE;

            if (isset($config['validation']['max_file_size'])) {
                $maxFileSize = (int)$config['validation']['max_file_size'];
            }

            $allowedExtensions = [];

            if (isset($config['validation']['file_extensions'])) {
                $allowedExtensions = explode(',', $config['validation']['file_extensions']);
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
            }

            $allowedExtensions = implode(' ', $allowedExtensions);

            $entityTypeCode = $entityType->getEntityTypeCode();
            $url = $this->getFileUploadUrl($entityTypeCode);

            $config = [
                'formElement' => 'fileUploader',
                'componentType' => 'fileUploader',
                'maxFileSize' => $maxFileSize,
                'allowedExtensions' => $allowedExtensions,
                'uploaderConfig' => [
                    'url' => $url,
                ],
                'label' => $this->getMetadataValue($config, 'label'),
                'sortOrder' => $this->getMetadataValue($config, 'sortOrder'),
                'required' => $this->getMetadataValue($config, 'required'),
                'visible' => $this->getMetadataValue($config, 'visible'),
                'validation' => $this->getMetadataValue($config, 'validation'),
            ];
        }
    }

    /**
     * Retrieve metadata value
     *
     * @param array $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getMetadataValue($config, $name, $default = null)
    {
        $value = isset($config[$name]) ? $config[$name] : $default;
        return $value;
    }

    /**
     * Retrieve URL to file upload
     *
     * @param string $entityTypeCode
     * @return string
     */
    private function getFileUploadUrl($entityTypeCode)
    {
        switch ($entityTypeCode) {
            case CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER:
                $url = 'customer/file/customer_upload';
                break;

            case AddressMetadataInterface::ENTITY_TYPE_ADDRESS:
                $url = 'customer/file/address_upload';
                break;

            default:
                $url = '';
                break;
        }
        return $url;
    }

    /**
     * Process attributes by their frontend input type
     *
     * @param AttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function processFrontendInput(AttributeInterface $attribute, array &$meta)
    {
        $attributeCode = $attribute->getAttributeCode();
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta[$attributeCode]['arguments']['data']['config']['prefer'] = 'toggle';
            $meta[$attributeCode]['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }
    }

    /**
     * Prepare address data for customer
     *
     * @param int $addressId
     * @param array $addresses
     * @param array $customer
     * @return void
     */
    protected function prepareAddressData($addressId, array &$addresses, array $customer)
    {
        if (isset($customer['default_billing'])
            && $addressId == $customer['default_billing']
        ) {
            $addresses[$addressId]['default_billing'] = $customer['default_billing'];
        }
        if (isset($customer['default_shipping'])
            && $addressId == $customer['default_shipping']
        ) {
            $addresses[$addressId]['default_shipping'] = $customer['default_shipping'];
        }
        if (isset($addresses[$addressId]['street']) && !is_array($addresses[$addressId]['street'])) {
            $addresses[$addressId]['street'] = explode("\n", $addresses[$addressId]['street']);
        }
    }
}
