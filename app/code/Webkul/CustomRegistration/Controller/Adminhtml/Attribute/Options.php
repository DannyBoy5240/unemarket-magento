<?php
/**
 * @category   Webkul
 * @package    Webkul_CustomRegistration
 * @author     Webkul Software Private Limited
 * @copyright   Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Controller\Adminhtml\Attribute;

use Magento\Framework\Controller\ResultFactory;

class Options extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     */
    private $categoryFactory;

    /**
     * ValidateTest constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('customregistration/*/');
        }

        $attributeOptions = $this->attributeFactory->create()->load($data['attr_id'])
                                     ->getSource()->getAllOptions(false);
        $attrOptionList = ['totalRecords' => count($attributeOptions), 'options' => $attributeOptions];
        return $this->resultJsonFactory->create()->setData($attrOptionList);
    }

    /**
     * Category map permission check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_CustomRegistration::index');
    }
}
