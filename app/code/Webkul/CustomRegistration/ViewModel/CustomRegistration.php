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
namespace Webkul\CustomRegistration\ViewModel;

use Webkul\CustomRegistration\Helper\Order as CustomOrderHelper;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Custom Registration View Model
 */
class CustomRegistration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var CustomOrderHelper
     */
    protected $customOrderHelper;

    /**
     * @var GiftMessageHelper
     */
    protected $giftMessageHelperl;

    /**
     * @param CustomOrderHelper $customOrderHelper
     * @param GiftMessageHelper $giftMessageHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        CustomOrderHelper $customOrderHelper,
        GiftMessageHelper $giftMessageHelper,
        JsonHelper $jsonHelper
    ) {
        $this->customOrderHelper = $customOrderHelper;
        $this->giftMessageHelper = $giftMessageHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get CustomRegistration Order Helper
     *
     * @return object \Webkul\CustomRegistration\Helper\Order
     */
    public function getCustomOrderHelper()
    {
        return $this->customOrderHelper;
    }

    /**
     * Get GiftMessage Helper
     *
     * @return object \Magento\GiftMessage\Helper\Message
     */
    public function getGiftMessageHelper()
    {
        return $this->giftMessageHelper;
    }

    /**
     * Get Json Helper
     *
     * @return object \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
