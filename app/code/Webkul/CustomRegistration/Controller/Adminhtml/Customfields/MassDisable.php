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

use Webkul\CustomRegistration\Controller\Adminhtml\AbstractMassStatus;

/**
 * Controller MassDisable
 */
class MassDisable extends AbstractMassStatus
{
    /**
     * Field id
     */
    public const ID_FIELD = 'entity_id';

    /**
     * @var string
     */
    protected $collection = \Webkul\CustomRegistration\Model\ResourceModel\Customfields\Collection::class;

    /**
     * @var string
     */
    protected $model = \Webkul\CustomRegistration\Model\Customfields::class;

    /**
     * @var boolean
     */
    protected $status = false;
}
