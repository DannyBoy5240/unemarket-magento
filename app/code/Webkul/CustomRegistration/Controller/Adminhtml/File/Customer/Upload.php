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
namespace Webkul\CustomRegistration\Controller\Adminhtml\File\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileUploader;
use Webkul\CustomRegistration\Model\FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;

class Upload extends Action
{
    /**
     * @var FileUploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploaderFactory $fileUploaderFactory
     * @param CustomerMetadataInterface $customerMetadataService
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        CustomerMetadataInterface $customerMetadataService,
        LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->customerMetadataService = $customerMetadataService;
        $this->logger = $logger;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            if (empty($this->getRequest()->getFiles('custom_registration'))) {
                throw new LocalizedException(__('$_FILES array is empty.'));
            }

            $attributeCode = key($this->getRequest()->getFiles('custom_registration'));
            $attributeMetadata = $this->customerMetadataService->getAttributeMetadata($attributeCode);

            // /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create([
                'attributeMetadata' => $attributeMetadata,
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'scope' => 'custom_registration',
            ]);

            $errors = $fileUploader->validate();
            if (true !== $errors) {
                $errorMessage = implode('</br>', $errors);
                throw new LocalizedException(__($errorMessage));
            }

            $result = $fileUploader->upload();
        } catch (LocalizedException $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
