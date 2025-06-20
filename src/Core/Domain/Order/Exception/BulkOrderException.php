<?php

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception;

use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;

class BulkOrderException extends OrderException
{
    /**
     * @var array<int, OrderException>
     */
    protected $bulkExceptions = [];

    public function addException(OrderId $orderId, OrderException $exception): void
    {
        $this->bulkExceptions[$orderId->getValue()] = $exception;
    }

    /**
     * @return OrderException[]
     */
    public function getBulkExceptions(): array
    {
        return $this->bulkExceptions;
    }
}
