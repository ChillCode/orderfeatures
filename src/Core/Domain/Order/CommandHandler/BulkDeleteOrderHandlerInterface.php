<?php

namespace PrestaShop\Module\OrderFeatures\Core\Domain\Order\CommandHandler;

use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\BulkDeleteOrderCommand;

/**
 * Interface for service that handles order deletion
 */
interface BulkDeleteOrderHandlerInterface
{
    /**
     * @param BulkDeleteOrderCommand $command
     */
    public function handle(BulkDeleteOrderCommand $command): void;
}
