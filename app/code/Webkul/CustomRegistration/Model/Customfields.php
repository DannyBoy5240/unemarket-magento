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

use Webkul\CustomRegistration\Api\Data\ManageFieldsInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Customfields extends \Magento\Framework\Model\AbstractModel implements ManageFieldsInterface, IdentityInterface
{

    /**
     * Fields's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * @var array
     */
    protected static $statusesOptions = [
        self::STATUS_ENABLED => 'Enabled',
        self::STATUS_DISABLED => 'Disabled',
    ];

    /**
     * @var int
     */
    public const IS_VISIBLE = 1;

    /**
     * @var int
     */
    public const NOT_VISIBLE = 0;

    /**
     * @var array
     */
    protected static $accountVisibleOptions = [
        self::IS_VISIBLE => 'Yes',
        self::NOT_VISIBLE => 'No',
    ];

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'custom_fields';

    /**
     * @var string
     */
    protected $_cacheTag = 'custom_fields';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'custom_fields';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\CustomRegistration\Model\ResourceModel\Customfields::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSeller();
        }

        return parent::load($id, $field);
    }

    /**
     * Get StatusOptionArray.
     *
     * @return array
     */
    public function getStatusOptionArray()
    {
        return self::$statusesOptions;
    }

    /**
     * Get VisibleOptionArray.
     *
     * @return array
     */
    public function getVisibleOptionArray()
    {
        return self::$accountVisibleOptions;
    }

    /**
     * Get AvailableStatuses. Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return self::$statusesOptions[$this->getStatus()];
    }

    /**
     * Prepare post's enable in account.
     *
     * @return array
     */
    public function getIsVisibleInAccount()
    {
        return self::$accountVisibleOptions[$this->getIsInSaif()];
    }

    /**
     * Load No-Route Seller.
     *
     * @return $this
     */
    public function noRouteSeller()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\CustomRegistration\Api\Data\ManageFieldsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set ID.
     *
     * @param string $status
     * @return \Webkul\CustomRegistration\Api\Data\ManageFieldsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
