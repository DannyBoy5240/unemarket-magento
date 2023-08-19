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
namespace Webkul\CustomRegistration\Helper;

use Magento\Framework\UrlInterface;

/**
 * Custom Registration Orders helper.
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFieldsFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFieldsFactory
    ) {
        $this->storeManager = $storeManager;
        $this->coreRegistry = $registry;
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->attributeCollection = $attributeCollection;
        $this->eavEntity = $eavEntity;
        $this->customerSession = $customerSession;
        $this->urlEncoder = $urlEncoder;
        $this->urlBuilder = $urlBuilder;
        $this->customFieldsFactory = $customFieldsFactory;
        parent::__construct($context);
    }
    /**
     * Check for custom attribute should be display in order view
     *
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInOrder($attrId)
    {
        $isShow = 0;
        $collection = $this->customFieldsFactory->create()->getCollection()
                                ->addFieldToFilter('attribute_id', ['eq'=>$attrId])
                                ->addFieldToFilter('show_in_order', ['eq'=>'1']);
        if (!empty($collection) && $collection->getSize() > 0) {
            $isShow = 1;
        }
        return $isShow;
    }
    /**
     * Check for custom attribute should be display in order email
     *
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInEmail($attrId)
    {
        $isShow = 0;
        $collection = $this->customFieldsFactory->create()->getCollection()
                            ->addFieldToFilter('attribute_id', ['eq'=>$attrId])
                            ->addFieldToFilter('show_in_email', ['eq'=>'1']);
        if (!empty($collection) && $collection->getSize() > 0) {
            $isShow = 1;
        }
        return $isShow;
    }
    /**
     * Get current customer data.
     *
     * @param  int $customerId
     */
    public function getCurrentCustomer($customerId)
    {
        $customerData = $this->customerFactory->create()->load($customerId);
        return $customerData;
    }
    /**
     * Retrieve order model.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }
    /**
     * EncodeFileName
     *
     * @param string $type
     * @param string $filePath
     * @return string
     */
    public function encodeFileName($type, $filePath)
    {
        $path = $this->request->getRouteName() == 'customregistration' ?
            'customregistration/media/view' : 'customer/index/viewfile';
        $url = $this->urlBuilder->getUrl(
            $path,
            [$type => $this->urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }

    /**
     * Get All custom attribute collection.
     *
     * @param int $websiteId
     * @param boolean $forOrder
     * @param boolean $forEmail
     * @return collection
     */
    public function attributeCollectionFilter($websiteId = null, $forOrder = false, $forEmail = false)
    {
        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $query = 'ccp.status = 1';
        if ($websiteId) {
            $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$websiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
        }
        if ($forOrder) {
            $query .= ' AND ccp.show_in_order = 1';
        }
        if ($forEmail) {
            $query .= ' AND ccp.show_in_email = 1';
        }
        $customField = $this->customFieldsFactory->create()->getCollection()->getTable('wk_customfields');
        $collection = $this->attributeCollection->create()
                ->setEntityTypeFilter($typeId)
                ->setOrder('sort_order', 'ASC');

        $collection->getSelect()
        ->join(
            ["ccp" => $customField],
            "ccp.attribute_id = main_table.attribute_id",
            ["status" => "status"]
        )->where($query);

        return $collection;
    }
}
