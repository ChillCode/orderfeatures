<?php

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\CommandHandler;

use PrestaShop\Module\OrderFeatures\Adapter\Order\OrderDeleter;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\DeleteOrderCommand;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\CommandHandler\DeleteOrderHandlerInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;

/**
 * Handles @see DeleteOrderCommand using legacy object model
 */
#[AsCommandHandler]
class DeleteOrderHandler implements DeleteOrderHandlerInterface
{
    /**
     * @var OrderDeleter
     */
    private $orderDeleter;

    public function __construct(
        OrderDeleter $orderDeleter
    ) {
        $this->orderDeleter = $orderDeleter;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DeleteOrderCommand $command): void
    {
        $this->orderDeleter->delete(
            $command->getOrderId()
        );
    }
}
