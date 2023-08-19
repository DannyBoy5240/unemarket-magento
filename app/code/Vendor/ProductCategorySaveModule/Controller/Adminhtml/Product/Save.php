<?php
namespace Vendor\ProductCategorySaveModule\Controller\Adminhtml\Product;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{
    public function execute()
    {
        var_dump('category updated!');
        // Get the category IDs from the request
        $categoryIds = $this->getRequest()->getParam('category_ids');

        // Get the total amount of selected categories
        $totalCategories = count($categoryIds);

        var_dump('total Categories');
        var_dump($totalCategories);

        // Perform custom validation on the category IDs
        if ($totalCategories !== 1) {
            $this->messageManager->addError(__('Please select exactly one category.'));
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            return;
        }

        // Get the first category ID from the array
        $categoryId = reset($categoryIds);

        // Load the category model
        $category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($categoryId);

        // Perform additional validation on the category, if necessary
        if (!$category->getId() || $category->getLevel() !== 2) {
            $this->messageManager->addError(__('Please select a valid second-level category.'));
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            var_dump('category additional validation');
            exit(0);
            return;
        }

        var_dump('final save part');
        exit(0);

        parent::execute();
    }
}
