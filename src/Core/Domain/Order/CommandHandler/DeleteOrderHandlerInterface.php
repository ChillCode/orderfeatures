<?php

namespace PrestaShop\Module\OrderFeatures\Core\Domain\Order\CommandHandler;

use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\DeleteOrderCommand;

/**
 * Defines contract to handle @see DeleteOrderCommand
 */
interface DeleteOrderHandlerInterface
{
    /**
     * @param DeleteOrderCommand $command
     */
    public function handle(DeleteOrderCommand $command): void;
}
