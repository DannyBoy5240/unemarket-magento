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
namespace Webkul\CustomRegistration\Plugin\Customer;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Api\Data\CustomerInterface;

class DataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    public const MAX_FILE_SIZE = 2097152;

    /**
     * @var string
     */
    protected $eavEntity;

    /**
     * @var string
     */
    protected $attributeCollection;

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

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
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * __construct
     *
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param EavValidationRules $eavValidationRules
     * @param Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Customer\Model\Config\Share $shareConfig,
        EavValidationRules $eavValidationRules,
        Config $eavConfig,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_eavEntity = $eavEntity;
        $this->_attributeCollection = $attributeCollection;
        $this->shareConfig = $shareConfig;
        $this->eavValidationRules = $eavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->_attributeFactory = $attributeFactory;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
    }

    /**
     * After GetMeta
     *
     * @param \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject
     * @param array $result
     * @return array $result
     */
    public function afterGetMeta(\Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject, $result)
    {
        $meta = $this->getAttributesMeta();
        if (!empty($meta)) {
            $result = array_replace_recursive(
                $result,
                [
                    'custom_registration' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Custom Registration Fields'),
                                    'componentType' => Fieldset::NAME,
                                    'collapsible' => false,
                                    'sortOrder' => 200,
                                ],
                            ],
                        ],
                        'children' => $meta
                    ]
                ]
            );
        }

        $result = $this->removeDuplicates($result);
        return $result;
    }

    /**
     * Remove duplicate attributes
     *
     * @param array $result
     * @return array
     */
    private function removeDuplicates($result)
    {
        try {
            $customAttributes = $this->getAttributeCollection(true);
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (in_array($attribute->getAttributeCode(), array_keys($result['customer']['children']))) {
                        unset($result['customer']['children'][$attribute->getAttributeCode()]);
                    }
                }
            }
        } catch (\Exception $e) {
            return $result;
        }

        return $result;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta()
    {
        $meta = [];
        $hiddenAttributesCode = [];
        $attributes = $this->getAttributeCollection();
        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $usedInForm = $this->getUsedInForms($attribute->getId());
            $showAttribute = (is_array($usedInForm) && in_array('adminhtml_customer', $usedInForm)) ? true : false;
            if (!$showAttribute) {
                $hiddenAttributesCode[] = $attribute->getAttributeCode();
                continue;
            }

            $this->processFrontendInput($attribute, $meta);
            $code = $attribute->getAttributeCode();
            $meta[$code]['arguments']['data']['config']['source'] = 'customer';
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);
                $meta[$code]['arguments']['data']['config'][$metaName] = ($metaName === 'label') ? __($value) : $value;
                if ($metaName === 'sortOrder') {
                    $meta[$code]['arguments']['data']['config'][$metaName] = is_numeric($value)
                    ? ($value * 10)
                    : $value;
                }
                if ('frontend_input' === $origName) {
                    $meta[$code]['arguments']['data']['config']['formElement'] = isset($this->formElement[$value])
                        ? $this->formElement[$value]
                        : $value;
                }
            }
            if ($attribute->usesSource()) {
                $meta[$code]['arguments']['data']['config']['options'] = $attribute->getSource()->getAllOptions(false);
            }

            $meta[$code]['arguments']['data']['config']['componentType'] = Field::NAME;
            $meta[$code]['arguments']['data']['config']['visible'] = true;
            $frontclass = explode(' ', $attribute->getFrontendClass());
            $isRequired = '';
            $rules = [];
            $cssClass = (in_array('dependable_field_'.$code, $frontclass)) ? 'dependable_field_'.$code : '';
            $resultClass = preg_grep('~' . 'child_' . '~', $frontclass);
            $requiredClass = preg_grep('~' . 'required' . '~', $frontclass);
            $validations = preg_grep('~' . 'validate' . '~', $frontclass);

            if (!empty($requiredClass)) {
                $isRequired = ' required';
                $rules['required-entry'] = 1;
            }
            if (!empty($validations)) {
                foreach ($validations as $validation) {
                    $rules[$validation] = 1;
                }
            }
            if (!empty($resultClass)) {
                $resultClass = array_values($resultClass);
                $cssClass = (isset($resultClass[0])) ? $resultClass[0] : '';
            }

            $meta[$code]['arguments']['data']['config']['validation'] = (!empty($rules)) ? $rules : [];
            if ($cssClass &&
                !empty(preg_grep('~' . str_replace('child_', '', $cssClass) . '~', $hiddenAttributesCode))
            ) {
                unset($meta[$code]);
                continue;
            }

            $meta[$code]['arguments']['data']['config']['additionalClasses'] = $cssClass.$isRequired;
            $this->overrideFileUploaderMetadata(
                $this->eavConfig->getEntityType('customer'),
                $attribute,
                $meta[$code]['arguments']['data']['config']
            );
        }

        return $meta;
    }

    /**
     * Override file uploader UI component metadata
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
                'notice' => $this->getMetadataValue($config, 'notice'),
                'additionalClasses'=> $this->getMetadataValue($config, 'additionalClasses'),
            ];
        }
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
                $url = 'customregistration/file/customer_upload';
                break;
            default:
                $url = '';
                break;
        }
        return $url;
    }

    /**
     * Process attributes by frontend input type
     *
     * @param AttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function processFrontendInput(AttributeInterface $attribute, array &$meta)
    {
        $code = $attribute->getAttributeCode();
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta[$code]['arguments']['data']['config']['prefer'] = 'toggle';
            $meta[$code]['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
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

        return (true && $canShowOnForm) ||
            (!true && $canShowOnForm && $customerAttribute->getIsVisible());
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
     * Add global scope parameter and filter options to website meta
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
     * Retrieve Customer Config Share
     *
     * @return \Magento\Customer\Model\Config\Share
     */
    private function getShareConfig()
    {
        return $this->shareConfig;
    }

    /**
     * Get UsedIn Forms
     *
     * @param  int $id
     * @return array
     */
    public function getUsedInForms($id)
    {
        $attributeModel = $this->_attributeFactory->create();
        return $attributeModel->load($id)->getUsedInForms();
    }

    /**
     * Return custom attributes collection
     *
     * @param boolean $status
     * @return \Magento\Customer\Model\ResourceModel\Attribute\Collection
     */
    private function getAttributeCollection($status = false)
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $query = 'ccp.status = 1';
        $customerId = $this->request->getParam('id');
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $websiteId = $customer->getWebsiteId();
            $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$websiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
        }
        $customField = $this->_attributeCollection->create()->getTable('wk_customfields');
        $collection = $this->_attributeCollection->create()
                ->setEntityTypeFilter($typeId)
                ->setOrder('sort_order', 'ASC');
        $collection->getSelect()
        ->join(
            ["ccp" => $customField],
            "ccp.attribute_id = main_table.attribute_id",
            ["status" => "status"]
        );

        if (!$status) {
            $collection->getSelect()->where($query);
        }
        return $collection;
    }
}
