<?php

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\CommandHandler;

use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\BulkOrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;

/**
 * Base class for bulk order command handlers.
 *
 * This class provides a common implementation for handling bulk actions on orders.
 * It iterates through the provided order IDs and applies the specified action to each one,
 * collecting results and exceptions as needed.
 */
abstract class AbstractBulkHandler
{
    /**
     * @param OrderId[] $orderIds
     * @param mixed|null $command
     *
     * @return array<int, mixed>
     *
     * @throws PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\BulkOrderException
     */
    protected function handleBulkAction(array $orderIds, $command = null): array
    {
        $bulkException = null;
        $actionResults = [];
        foreach ($orderIds as $orderId) {
            try {
                $actionResults[$orderId->getValue()] = $this->handleSingleAction($orderId, $command);
            } catch (OrderException $e) {
                if (null === $bulkException) {
                    $bulkException = $this->buildBulkException();
                }
                $bulkException->addException($orderId, $e);
            }
        }

        if (null !== $bulkException) {
            throw $bulkException;
        }

        return $actionResults;
    }

    /**
     * This uses the base bulk exception class, but you can override this in your handler.
     *
     * @return BulkOrderException
     */
    protected function buildBulkException(): BulkOrderException
    {
        return new BulkOrderException();
    }

    /**
     * @param OrderId $orderId
     * @param mixed|null $command
     *
     * @return mixed
     */
    abstract protected function handleSingleAction(OrderId $orderId, $command = null);
}
