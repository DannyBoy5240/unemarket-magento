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

namespace Webkul\CustomRegistration\Block\Adminhtml;

class SupportLinks extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Module Current version
     *
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data = $this->jsonHelper->jsonDecode($composerJsonData);

        return !empty($data['version']) ? $data['version'] : __('Read error!');
    }
    
    /**
     * Get Html Element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $moduleCode = 'Webkul_CustomRegistration';
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<div><p>'.__('Author').': 
        <a target="_blank" title="Webkul Software Private Limited" href="https://webkul.com/">'.__('Webkul').'</a></p>
        <p>'.__('Version').': '.$this->getModuleVersion($moduleCode).'</p>
        <p>'.__('User Guide').': 
        <a target="_blank" href="https://webkul.com/blog/custom-registration-field-for-magento2/">'
        .__('Click Here').'</a></p>
        <p>'.__('Store Extension').': 
        <a target="_blank" href="https://store.webkul.com/Magento-2/Magento2-Custom-Registration-Field.html">'
        .__('Click Here').'</a></p>
        <p>'.__('Ticket/Customisations').': 
        <a target="_blank" href="https://webkul.uvdesk.com/en/customer/create-ticket/">'.__('Click Here').'</a></p>
        <p>'.__('Services').': <a target="_blank" 
        href="https://webkul.com/magento-development/">'.__('Click Here').'</a></p></div>';
        return $html;
    }
}
