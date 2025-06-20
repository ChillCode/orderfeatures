<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\CommandHandler;

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
        OrderDeleter $orderDeleter,
    ) {
        $this->orderDeleter = $orderDeleter;
    }

    /**
     * {@inheritdoc}
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
            throw new \InvalidArgumentException(sprintf('Expected argument $command of type "%s". Got "%s"', BulkDeleteOrderCommand::class, var_export($command, true)));
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
