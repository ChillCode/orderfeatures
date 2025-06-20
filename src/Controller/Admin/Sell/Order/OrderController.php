<?php

/**
 * @author    ChillCode <https://github.com/chillcode>
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\OrderFeatures\Controller\Admin\Sell\Order;

use Exception;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\BulkDeleteOrderCommand;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Command\DeleteOrderCommand;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\BulkOrderException;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\CannotBulkDeleteOrderException;
use PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception\CannotDeleteOrderException;
use PrestaShop\PrestaShop\Adapter\Currency\CurrencyDataProvider;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\PDF\OrderInvoicePdfGenerator;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Form\ChoiceProvider\LanguageByIdChoiceProvider;
use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\OrderGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Kpi\Row\KpiRowFactoryInterface;
use PrestaShop\PrestaShop\Core\Order\OrderSiblingProviderInterface;
use PrestaShop\PrestaShop\Core\PDF\PDFGeneratorInterface;
use PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Controller\Admin\Sell\Order\OrderController as OrderControllerCore;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use PrestaShopBundle\Security\Attribute\DemoRestricted;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class OrderController.
 *
 * @ModuleActivated(moduleName="orderfeatures", redirectRoute="admin_module_manage")
 */
class OrderController extends PrestaShopAdminController
{
    /**
     * @var OrderControllerCore
     */
    private $orderControllerCore;

    public function __construct(
        ?OrderControllerCore $orderControllerCore
    ) {
        $this->orderControllerCore = $orderControllerCore;
    }

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function generateInvoicePdfAction(
        int $orderId,
        OrderInvoicePdfGenerator $invoicePdfGenerator
    ): BinaryFileResponse {
        return $this->orderControllerCore->generateInvoicePdfAction(
            $orderId,
            $invoicePdfGenerator
        );
    }

    public function generateDeliverySlipPdfAction(
        int $orderId,
        PDFGeneratorInterface $deliverySlipPdfGenerator
    ): BinaryFileResponse {
        return $this->orderControllerCore->generateDeliverySlipPdfAction(
            $orderId,
            $deliverySlipPdfGenerator
        );
    }

    public function changeOrdersStatusAction(
        Request $request
    ) {
        return $this->orderControllerCore->changeOrdersStatusAction(
            $request
        );
    }

    public function exportAction(
        OrderFilters $filters,
        GridFactory $orderGridFactory
    ) {
        return $this->orderControllerCore->exportAction(
            $filters,
            $orderGridFactory
        );
    }

    public function partialRefundAction(
        int $orderId,
        Request $request,
        FormBuilderInterface $formBuilder,
        FormHandlerInterface $formHandler
    ) {
        return $this->orderControllerCore->partialRefundAction(
            $orderId,
            $request,
            $formBuilder,
            $formHandler
        );
    }

    public function standardRefundAction(
        int $orderId,
        Request $request,
        FormBuilderInterface $formBuilder,
        FormHandlerInterface $formHandler
    ) {
        return $this->orderControllerCore->standardRefundAction(
            $orderId,
            $request,
            $formBuilder,
            $formHandler
        );
    }

    public function returnProductAction(
        int $orderId,
        Request $request,
        FormBuilderInterface $formBuilder,
        FormHandlerInterface $formHandler
    ) {
        return $this->orderControllerCore->returnProductAction(
            $orderId,
            $request,
            $formBuilder,
            $formHandler
        );
    }

    public function addProductAction(
        int $orderId,
        Request $request,
        FormBuilderInterface $formBuilder,
        CurrencyDataProvider $currencyDataProvider
    ): Response {
        return $this->orderControllerCore->addProductAction(
            $orderId,
            $request,
            $formBuilder,
            $currencyDataProvider
        );
    }

    public function getProductPricesAction(
        int $orderId
    ): Response {
        return $this->orderControllerCore->getProductPricesAction(
            $orderId
        );
    }

    public function getInvoicesAction(
        int $orderId,
        #[AutowireDecorated] ConfigurableFormChoiceProviderInterface $choiceProvider
    ) {
        return $this->orderControllerCore->getInvoicesAction(
            $orderId,
            $choiceProvider
        );
    }

    public function getDocumentsAction(
        int $orderId
    ) {
        return $this->orderControllerCore->getDocumentsAction(
            $orderId
        );
    }

    public function getShippingAction(
        int $orderId,
    ) {
        return $this->orderControllerCore->getShippingAction(
            $orderId
        );
    }

    public function updateShippingAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->updateShippingAction(
            $orderId,
            $request
        );
    }

    public function removeCartRuleAction(
        int $orderId,
        int $orderCartRuleId
    ): RedirectResponse {
        return $this->orderControllerCore->removeCartRuleAction(
            $orderId,
            $orderCartRuleId
        );
    }

    public function updateInvoiceNoteAction(
        int $orderId,
        int $orderInvoiceId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->updateInvoiceNoteAction(
            $orderId,
            $orderInvoiceId,
            $request
        );
    }

    public function updateProductAction(
        int $orderId,
        int $orderDetailId,
        Request $request,
        #[AutowireDecorated] FormBuilderInterface $formBuilder,
        CurrencyDataProvider $currencyDataProvider
    ): Response {
        return $this->orderControllerCore->updateProductAction(
            $orderId,
            $orderDetailId,
            $request,
            $formBuilder,
            $currencyDataProvider
        );
    }

    public function addCartRuleAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->addCartRuleAction(
            $orderId,
            $request
        );
    }

    public function updateStatusAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->updateStatusAction(
            $orderId,
            $request
        );
    }

    public function updateStatusFromListAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->updateStatusFromListAction(
            $orderId,
            $request
        );
    }

    public function addPaymentAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->addPaymentAction(
            $orderId,
            $request
        );
    }

    public function duplicateOrderCartAction(
        int $orderId,
    ) {
        return $this->orderControllerCore->duplicateOrderCartAction(
            $orderId
        );
    }

    public function sendMessageAction(
        Request $request,
        int $orderId,
        #[AutowireDecorated] RouterInterface $router
    ): Response {
        return $this->orderControllerCore->sendMessageAction(
            $request,
            $orderId,
            $router
        );
    }

    public function changeCustomerAddressAction(Request $request): RedirectResponse
    {
        return $this->orderControllerCore->changeCustomerAddressAction($request);
    }

    public function changeCurrencyAction(
        int $orderId,
        Request $request
    ): RedirectResponse {
        return $this->orderControllerCore->changeCurrencyAction(
            $orderId,
            $request
        );
    }

    public function resendEmailAction(
        int $orderId,
        int $orderStatusId,
        int $orderHistoryId
    ): RedirectResponse {
        return $this->orderControllerCore->resendEmailAction(
            $orderId,
            $orderStatusId,
            $orderHistoryId
        );
    }

    public function deleteProductAction(
        int $orderId,
        int $orderDetailId
    ): JsonResponse {
        return $this->orderControllerCore->deleteProductAction(
            $orderId,
            $orderDetailId
        );
    }

    public function getDiscountsAction(
        int $orderId
    ): Response {
        return $this->orderControllerCore->getDiscountsAction(
            $orderId
        );
    }

    public function getPricesAction(
        int $orderId
    ): JsonResponse {
        return $this->orderControllerCore->getPricesAction(
            $orderId
        );
    }

    public function getPaymentsAction(
        int $orderId
    ): Response {
        return $this->orderControllerCore->getPaymentsAction(
            $orderId
        );
    }

    public function getProductsListAction(
        int $orderId,
        #[AutowireDecorated] FormBuilderInterface $formBuilder,
        CurrencyDataProvider $currencyDataProvider
    ): Response {
        return $this->orderControllerCore->getProductsListAction(
            $orderId,
            $formBuilder,
            $currencyDataProvider
        );
    }

    public function generateInvoiceAction(
        int $orderId
    ): RedirectResponse {
        return $this->orderControllerCore->generateInvoiceAction(
            $orderId
        );
    }

    public function sendProcessOrderEmailAction(
        Request $request
    ): JsonResponse {
        return $this->orderControllerCore->sendProcessOrderEmailAction(
            $request
        );
    }

    public function cancellationAction(
        int $orderId,
        Request $request,
        #[AutowireDecorated] FormBuilderInterface $formBuilder,
        #[AutowireDecorated] FormHandlerInterface $formHandler,
    ) {
        return $this->orderControllerCore->cancellationAction(
            $orderId,
            $request,
            $formBuilder,
            $formHandler
        );
    }

    public function configureProductPaginationAction(Request $request): JsonResponse
    {
        return $this->orderControllerCore->configureProductPaginationAction(
            $request
        );
    }

    public function displayCustomizationImageAction(
        int $orderId,
        string $value,
        LegacyContext $context
    ) {
        return $this->orderControllerCore->displayCustomizationImageAction(
            $orderId,
            $value,
            $context
        );
    }

    public function setInternalNoteAction(
        int $orderId,
        Request $request
    ) {
        return $this->orderControllerCore->setInternalNoteAction(
            $orderId,
            $request
        );
    }

    public function searchProductsAction(
        Request $request
    ): JsonResponse {
        return $this->orderControllerCore->searchProductsAction(
            $request
        );
    }

    public function indexAction(
        Request $request,
        OrderFilters $filters,
        KpiRowFactoryInterface $orderKpiFactory,
        GridFactory $orderGridFactory
    ) {
        return $this->orderControllerCore->indexAction(
            $request,
            $filters,
            $orderKpiFactory,
            $orderGridFactory
        );
    }

    public function viewAction(
        int $orderId,
        Request $request,
        FormBuilderInterface $formBuilder,
        OrderSiblingProviderInterface $orderSiblingProvider,
        CurrencyDataProvider $currencyDataProvider
    ): Response {
        return $this->orderControllerCore->viewAction(
            $orderId,
            $request,
            $formBuilder,
            $orderSiblingProvider,
            $currencyDataProvider
        );
    }

    public function placeAction(
        Request $request,
        FormHandlerInterface $formHandler
    ) {
        return $this->orderControllerCore->placeAction(
            $request,
            $formHandler
        );
    }

    public function createAction(
        Request $request,
        LanguageByIdChoiceProvider $languageChoiceProvider,
        FormChoiceProviderInterface $currencyChoiceProvider
    ) {
        return $this->orderControllerCore->createAction(
            $request,
            $languageChoiceProvider,
            $currencyChoiceProvider
        );
    }

    public function searchAction(
        Request $request,
        OrderGridDefinitionFactory $orderGridDefinitionFactory
    ) {
        return $this->orderControllerCore->searchAction(
            $request,
            $orderGridDefinitionFactory
        );
    }

    public function previewAction(
        int $orderId
    ): JsonResponse {
        return $this->orderControllerCore->previewAction(
            $orderId
        );
    }

    /**
     * Delete order.
     *
     * TODO: To add a deleteAction we could use GET method (not recommended, so is not released) or to load submit in WebPack js file, since i don't want to distribute js will wait till is loaded like the rest.
     */
    #[AdminSecurity("is_granted('delete', 'AdminOrders')", message: 'You do not have permission to delete this.', redirectQueryParamsToKeep: ['orderId'], redirectRoute: 'admin_orders_view')]
    private function deleteAction(
        OrderId $orderId
    ) {
        try {
            $this->dispatchCommand(new DeleteOrderCommand($orderId));

            $this->addFlash(
                'success',
                $this->trans('The order has been successfully deleted.', [], 'Admin.Notifications.Success')
            );
        } catch (Exception $e) {
            $this->addFlash(
                'error',
                $this->trans($this->getErrorMessageForException($e, $this->getErrorMessages()), [], 'Admin.Notifications.Success')
            );
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Process bulk orders deletion.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    #[AdminSecurity("is_granted('delete', 'AdminOrders')", message: 'You do not have permission to edit this.', redirectQueryParamsToKeep: ['orderId'], redirectRoute: 'admin_orders_view')]
    #[DemoRestricted(message: 'You cannot delete orders in demo mode.')]
    public function bulkDeleteAction(
        Request $request
    ) {
        $ordersToDelete = array_map('intval', $request->request->all('order_orders_bulk'));

        try {
            $this->dispatchCommand(new BulkDeleteOrderCommand($ordersToDelete));

            $this->addFlash(
                'success',
                $this->trans('The selection has been successfully deleted.', [], 'Admin.Notifications.Success')
            );
        } catch (Exception $e) {
            if ($e instanceof BulkOrderException) {
                return $this->jsonBulkErrors($e);
            } else {
                $this->addFlash(
                    'error',
                    $this->trans($this->getErrorMessageForException($e, $this->getErrorMessages()), [], 'Admin.Notifications.Success')
                );
            }
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Format the bulk exception into an array of errors returned in a RedirectResponse.
     *
     * @param BulkOrderException $bulkOrderException
     *
     * @return JsonResponse
     */
    private function jsonBulkErrors(BulkOrderException $bulkOrderException): RedirectResponse
    {
        foreach ($bulkOrderException->getBulkExceptions() as $id_order => $orderException) {
            $this->addFlash(
                'error',
                $this->trans($this->getErrorMessageForException($orderException, $this->getErrorMessages()), [], 'Admin.Notifications.Notification')
            );
        }

        return $this->redirectToRoute('admin_orders_index');
    }

    /**
     * Gets an error by exception class and its code.
     *
     * @return array
     */
    private function getErrorMessages(): array
    {
        return [
            CannotDeleteOrderException::class => $this->trans(
                'An error occurred while deleting the order.',
                [],
                'Admin.Notifications.Error'
            ),
            CannotBulkDeleteOrderException::class => $this->trans(
                'An error occurred while deleting the selection.',
                [],
                'Admin.Notifications.Error'
            ),
        ];
    }
}
