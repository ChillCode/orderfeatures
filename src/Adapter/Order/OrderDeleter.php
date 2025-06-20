<?php

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Adapter\Order;

use PrestaShop\Module\OrderFeatures\Adapter\Order\Repository\OrderRepository;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;

class OrderDeleter
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderId $orderId
     */
    public function delete(OrderId $orderId): void
    {
        $this->orderRepository->delete($orderId);
    }
}
