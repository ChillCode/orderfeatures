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
