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

namespace PrestaShop\Module\OrderFeatures\Adapter\OrderState\CommandHandler;

use PrestaShop\Module\OrderFeatures\Core\Domain\OrderState\Command\EditOrderStateCommand;
use PrestaShop\Module\OrderFeatures\Core\Domain\OrderState\CommandHandler\EditOrderStateHandlerInterface;
use PrestaShop\PrestaShop\Adapter\OrderState\CommandHandler\AbstractOrderStateHandler;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\OrderState\Exception\MissingOrderStateRequiredFieldsException;
use PrestaShop\PrestaShop\Core\Domain\OrderState\Exception\OrderStateException;
use PrestaShop\PrestaShop\Core\Domain\OrderState\OrderStateFileUploaderInterface;

/**
 * Handles commands which edits given order state with provided data.
 *
 * @internal
 */
#[AsCommandHandler]
final class EditOrderStateHandler extends AbstractOrderStateHandler implements EditOrderStateHandlerInterface
{
    /**
     * @var OrderStateFileUploaderInterface
     */
    protected $fileUploader;

    /**
     * @param OrderStateFileUploaderInterface $fileUploader
     */
    public function __construct(OrderStateFileUploaderInterface $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EditOrderStateCommand $command)
    {
        $orderStateId = $command->getOrderStateId();
        $orderState = new \OrderState($orderStateId->getValue());

        $this->assertOrderStateWasFound($orderStateId, $orderState);

        $this->updateOrderStateWithCommandData($orderState, $command);

        $this->assertRequiredFieldsAreNotMissing($orderState);

        if (false === $orderState->validateFields(false)) {
            throw new OrderStateException('OrderState contains invalid field values');
        }

        if (false === $orderState->update()) {
            throw new OrderStateException('Failed to update order state');
        }

        if ($command->getFilePathName()) {
            $this->fileUploader->upload(
                $command->getFilePathName(),
                $orderStateId->getValue(),
                $command->getFileSize()
            );
        }
    }

    /**
     * @throws MissingOrderStateRequiredFieldsException
     */
    protected function assertRequiredFieldsAreNotMissing(\OrderState $orderState)
    {
        // Check that we have templates for all languages when send_email is on
        $haveMissingTemplates = (
            !is_array($orderState->template)
            || count($orderState->template) != count(array_filter($orderState->template, function ($v) {
                return (bool) strlen($v);
            }))
        );

        if (true === $orderState->send_email && true === $haveMissingTemplates) {
            throw new MissingOrderStateRequiredFieldsException(['template'], 'One or more required fields for order state are missing. Missing fields are: template');
        }

        parent::assertRequiredFieldsAreNotMissing($orderState);
    }

    private function updateOrderStateWithCommandData(\OrderState $orderState, EditOrderStateCommand $command)
    {
        if (null !== $command->getName()) {
            $orderState->name = $command->getName();
        }

        if (null !== $command->getColor()) {
            $orderState->color = $command->getColor();
        }

        if (null !== $command->isLoggable()) {
            $orderState->logable = $command->isLoggable();
        }

        if (null !== $command->isHidden()) {
            $orderState->hidden = $command->isHidden();
        }

        if (null !== $command->isInvoice()) {
            $orderState->invoice = $command->isInvoice();
        }

        if (null !== $command->isSendEmailEnabled()) {
            $orderState->send_email = $command->isSendEmailEnabled();

            if ($orderState->send_email && null !== $command->getTemplate()) {
                $orderState->template = $command->getTemplate();
            }
        }

        if (null !== $command->isSendEmailWarehouse()) {
            $orderState->send_email_warehouse = $command->isSendEmailWarehouse();
            if ($orderState->send_email_warehouse) {
                if (!empty($command->getEmailWarehouse())) {
                    $orderState->email_warehouse = $command->getEmailWarehouse();
                }
                if (!empty($command->getWarehouseTemplate())) {
                    $orderState->warehouse_template = $command->getWarehouseTemplate();
                }
            }
        }

        if (null !== $command->isPdfInvoice()) {
            $orderState->pdf_invoice = $command->isPdfInvoice();
        }

        if (null !== $command->isPdfDelivery()) {
            $orderState->pdf_delivery = $command->isPdfDelivery();
        }

        if (null !== $command->isShipped()) {
            $orderState->shipped = $command->isShipped();
        }

        if (null !== $command->isPaid()) {
            $orderState->paid = $command->isPaid();
        }

        if (null !== $command->isDelivery()) {
            $orderState->delivery = $command->isDelivery();
        }
    }
}
