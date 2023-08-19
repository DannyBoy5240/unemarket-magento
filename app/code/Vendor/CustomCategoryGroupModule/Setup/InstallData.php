<?php
namespace Vendor\CustomCategoryGroupModule\Setup;

use Magento\Framework\Setup\{
    ModuleContextInterface,
    ModuleDataSetupInterface,
    InstallDataInterface
};

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'payment_method', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'varchar',
            'label'    => 'Payment Method',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'direct_bonus_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Direct Bonus',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'reserve_for_matching_bonus_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Reserve for Matching Bonus',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'reserve_for_team_bonus_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Reserve for Team Bonus',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'reserve_for_range_bonus_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Reserve for Range Bonus',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'reserve_for_anual_bonus_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Reserve for Anual Bonus',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'genu_revenues_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'GENU revenues',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'salesman_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Salesman',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'community_fund_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Community fund',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'liquidity_fund_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Liquidity fund',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'infrastructure_expenses_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Infrastructure expenses',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'i_d_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => '- I+D',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'others_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => '- Others *',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'founders_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Founders',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'ambassadors_attr', [
            'group'    => 'Custom Values Group For Smart Contract',
            'type'     => 'int',
            'label'    => 'Ambassadors',
            'input'    => 'text',
            'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'visible'  => true,
            'default'  => 0,
            'required' => true,
            'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
        ]);
    }
}
