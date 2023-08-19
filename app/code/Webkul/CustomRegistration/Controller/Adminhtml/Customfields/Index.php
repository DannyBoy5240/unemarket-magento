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

namespace Webkul\CustomRegistration\Controller\Adminhtml\Customfields;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * _isAllowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_CustomRegistration::customregistration');
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\PageFactory $resultPage
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Webkul_CustomRegistration::index');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Custom Fields'));
        $resultPage->addBreadcrumb(__('Manage Custom Fields'), __('Manage Custom Fields'));
        return $resultPage;
    }
}
