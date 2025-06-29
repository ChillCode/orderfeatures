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

namespace PrestaShop\Module\OrderFeatures\Command;

use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\DeleteOrderCommand;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOrdersCommand extends Command
{
    protected static $defaultName = 'orderfeatures:delete-orders';

    public function __construct(
        private readonly CommandBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes orders by ID.')
            ->addArgument('orderIds', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Space-separated list of order IDs to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orderIds = $input->getArgument('orderIds');

        foreach ($orderIds as $orderId) {
            try {
                $this->commandBus->handle(new DeleteOrderCommand((int) $orderId));
                $output->writeln("<info>Order ID {$orderId} deleted.</info>");
            } catch (\Throwable $e) {
                $output->writeln("<error>Failed to delete order: {$e->getMessage()}</error>");
            }
        }

        return Command::SUCCESS;
    }
}
