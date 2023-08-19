<?php

namespace Vendor\CustomCategoryGroupModule\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template;
use Magento\Catalog\Model\CategoryFactory;

class CustomGroup extends Template
{
    protected $_categoryFactory;

    public function __construct(
        Template\Context $context,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_categoryFactory = $categoryFactory;
    }

    public function canShowTab()
    {
        $categoryId = $this->getRequest()->getParam('id');
        $category = $this->_categoryFactory->create()->load($categoryId);

        var_dump($categoryId);
        var_dump($category->getId());
        var_dump($category->getParentId());
        var_dump($category->getLevel());
        exit(0);

        if (!$category->getId()) {
            return false;
        }
        if ($category->getParentId() == 2 && $category->getLevel() == 2) {
            return true;
        }
        return false;
    }
}
