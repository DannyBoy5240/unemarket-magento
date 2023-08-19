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
namespace Webkul\CustomRegistration\Api\Data;

/**
 * CustomRegistration Manage Fields interface.
 * @api
 */
interface ManageFieldsInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'entity_id';
    public const STATUS = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\CustomRegistration\Api\Data\ManageFieldsInterface
     */
    public function setId($id);
     /**
      * Get ID
      *
      * @return int|null
      */
    public function getStatus();

    /**
     * Set ID
     *
     * @param int $status
     * @return \Webkul\CustomRegistration\Api\Data\ManageFieldsInterface
     */
    public function setStatus($status);
}
