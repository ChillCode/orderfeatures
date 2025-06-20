<?php

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\CommandHandler;

use InvalidArgumentException;
use PrestaShop\Module\OrderFeatures\Adapter\Order\OrderDeleter;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\BulkDeleteOrderCommand;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\CommandHandler\BulkDeleteOrderHandlerInterface;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\BulkOrderException;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\CannotBulkDeleteOrderException;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;

/**
 * Handles command which deletes orders in bulk action
 */
#[AsCommandHandler]
final class BulkDeleteOrderHandler extends AbstractBulkHandler implements BulkDeleteOrderHandlerInterface
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
     *
     * @throws BulkDeletOrderException
     */
    public function handle(BulkDeleteOrderCommand $command): void
    {
        $this->handleBulkAction($command->getOrderIds(), $command);
    }

    /**
     * @param OrderId $orderId
     * @param BulkDeleteOrderCommand|null $command
     *
     * @return void
     */
    protected function handleSingleAction(OrderId $orderId, $command = null): void
    {
        if (!($command instanceof BulkDeleteOrderCommand)) {
            throw new InvalidArgumentException(sprintf('Expected argument $command of type "%s". Got "%s"', BulkDeleteOrderCommand::class, var_export($command, true)));
        }

        $this->orderDeleter->delete(
            $orderId
        );
    }

    protected function buildBulkException(): BulkOrderException
    {
        return new CannotBulkDeleteOrderException();
    }
}
