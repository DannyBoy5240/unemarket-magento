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
namespace Webkul\CustomRegistration\Model;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class FileUploader
{
    /**
     * @param ElementFactory $elementFactory
     * @param FileProcessorFactory $fileProcessorFactory
     * @param Filesystem $filesystem
     * @param IoFile $ioFile
     * @param AttributeMetadataInterface $attributeMetadata
     * @param UploaderFactory $uploaderFactory
     * @param string $entityTypeCode
     * @param string $scope
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        ElementFactory $elementFactory,
        FileProcessorFactory $fileProcessorFactory,
        Filesystem $filesystem,
        IoFile $ioFile,
        AttributeMetadataInterface $attributeMetadata,
        UploaderFactory $uploaderFactory,
        $entityTypeCode,
        $scope,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->elementFactory = $elementFactory;
        $this->fileProcessorFactory = $fileProcessorFactory;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->attributeMetadata = $attributeMetadata;
        $this->uploaderFactory = $uploaderFactory;
        $this->entityTypeCode = $entityTypeCode;
        $this->scope = $scope;
        $this->request = $request;
    }

    /**
     * Validate uploaded file
     *
     * @return array|bool
     */
    public function validate()
    {
        $formElement = $this->elementFactory->create(
            $this->attributeMetadata,
            null,
            $this->entityTypeCode
        );

        $errors = $formElement->validateValue($this->getData());
        return $errors;
    }

    /**
     * Execute file uploading
     *
     * @return \string[]
     * @throws LocalizedException
     */
    public function upload()
    {
        $allowedExtensions = $this->getAllowedExtensions();

        if (count($allowedExtensions) == 0) {
            $allowedExtensions = explode(',', $this->attributeMetadata->getNote());
        }
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $fileId = $this->scope . '[' . $this->getAttributeCode() . ']';
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions($allowedExtensions);
        $fileInfo = $uploader->validateFile();
        $path = $this->mediaDirectory->getAbsolutePath(
            $this->entityTypeCode . '/' . FileProcessor::TMP_DIR
        );
        $ext = $this->ioFile->getPathInfo($fileInfo['name']);
        $newFileName = rand(10, 99999).'-'.time().'.'.$ext['extension'];

        $result = $uploader->save($path, $newFileName);
        $result['name'] = $newFileName;
        unset($result['path']);

        $path = "";
        if (isset($result['path'])) {
            $path = $result['path'].'/';
        } else {
            $path = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
                $this->entityTypeCode. '/' . FileProcessor::TMP_DIR
            );
        }

        $fileProcessor = $this->fileProcessorFactory->create([
            'entityTypeCode' => $this->entityTypeCode,
            'allowedExtensions' => $allowedExtensions,
        ]);
        $result['url'] = $fileProcessor->getViewUrl(
            FileProcessor::TMP_DIR . '/' . ltrim($result['file'], '/'),
            $this->attributeMetadata->getFrontendInput()
        );
        return $result;
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    private function getAttributeCode()
    {
        return key($this->request->getFiles('custom_registration'));
    }

    /**
     * Retrieve data from global $_FILES array
     *
     * @return array
     */
    private function getData()
    {
        $data = $this->request->getFiles('custom_registration');
        if (!empty($data) && isset($data[$this->getAttributeCode()])) {
            return $data[$this->getAttributeCode()];
        }
        return [];
    }

    /**
     * Get AllowedExtensions
     *
     * @return array
     */
    private function getAllowedExtensions()
    {
        $allowedExtensions = [];
        $validationRules = $this->attributeMetadata->getValidationRules();
        foreach ($validationRules as $validationRule) {
            if ($validationRule->getName() == 'file_extensions') {
                $allowedExtensions = explode(',', $validationRule->getValue());
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
                break;
            }
        }
        return $allowedExtensions;
    }
}
