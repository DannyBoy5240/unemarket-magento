<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Crypto\Base\Plugin;

use Magento\Sales\Model\Service\OrderService as BaseOrderService;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Math\Random;

class OrderService
{
    protected Random $random;

    public function __construct(
        Random $random
    ) {
        $this->random = $random;
    }

    public function beforePlace(
        BaseOrderService $subject,
        OrderInterface $order
    ): array
    {
        try {
            $prefix = bin2hex(random_bytes(10));
        } catch (\Exception $ex) {
            $prefix = $this->random->getRandomString(10);
        }

        $suffix = hash('sha256', (string) time());
        $body = $prefix . $order->getIncrementId() . $order->getEntityId() . $suffix;
        $orderHash = hash('sha256', $body);
        //@phpstan-ignore-next-line
        $order->setOrderHash($orderHash);

        return [$order];
    }
}
