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
namespace Webkul\CustomRegistration\Model\Customer\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Filesystem\DriverInterface;

class File extends \Magento\Eav\Model\Entity\Attribute\Backend\Increment
{
    /**
     * @var string
     */
    protected $_type = 'file';

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Filesystem facade.
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
    }

     /**
      * Save uploaded file and set its name to category
      *
      * @param \Magento\Framework\DataObject $object
      * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
      */
    public function afterSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value = $this->request->getPostValue();
        $attributeData = $this->getAttribute()->getData();

        if (array_key_exists('frontend_input', $attributeData) && $attributeData['frontend_input'] == 'file') {
            $this->_type = 'file';
            try {
                $savedValue = (isset($value['customer'][$attributeCode])) ? $value['customer'][$attributeCode] : '';
                if (isset($value['customfield_'.$attributeCode]['delete']) &&
                    $value['customfield_'.$attributeCode]['delete'] == 1) {
                    $object->setData($this->getAttribute()->getName(), '');
                    $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
                    return $this;
                }
                if (isset($value[$attributeCode]['delete']) && $value[$attributeCode]['delete'] == 1) {
                    $object->setData($this->getAttribute()->getName(), '');
                    $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
                    return $this;
                }

                $path = $this->_filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath(
                    'customer/'
                );

                if (is_array($value) && !empty($value['delete'])) {
                    $object->setData($this->getAttribute()->getName(), '');
                    $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
                    return $this;
                }
                $allowedExtensions = explode(',', $this->getAttribute()->getNote());

                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                $uploader = $this->_fileUploaderFactory->create(['fileId' => $this->getAttribute()->getName()]);
                $uploader->setAllowedExtensions($allowedExtensions);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($path);
                $object->setData($this->getAttribute()->getName(), $result['file']);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            } catch (\Exception $e) {
                // if no image was set - save previous image value
                $filteredSavedValue = "";
                if (is_array($savedValue)) {
                    $filteredSavedValue =  $savedValue[0]['file'];
                    if ($filteredSavedValue != '' && (!isset($value['customfield_'.$attributeCode]['delete']))) {
                        $object->setData($this->getAttribute()->getName(), $filteredSavedValue);
                        $this->getAttribute()->getEntity()->saveAttribute(
                            $object,
                            $this->getAttribute()->getName()
                        );
                    }
                } elseif ($savedValue != '') {
                    $object->setData($this->getAttribute()->getName(), $savedValue);
                    $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
                }
                return $this;
            }
        }
        return $this;
    }
}
