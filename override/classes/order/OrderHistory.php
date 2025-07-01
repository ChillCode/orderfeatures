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

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Generic.Files.LineLength.TooLong
/**
 * Overriding is never a nice choice but there are no hooks here and src/ references this ObjectModel.
 * Send an additional email to warehouse adding order reference in the subject.
 */
class OrderHistory extends OrderHistoryCore
{
    public function sendEmail($order, $template_vars = false)
    {
        $result = Db::getInstance()->getRow('
            SELECT osl.`template`, osl.`warehouse_template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`, os.`send_email` AS send_email, os.`send_email_warehouse` AS send_email_warehouse, os.`email_warehouse` AS email_warehouse
            FROM `' . _DB_PREFIX_ . 'order_history` oh
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON oh.`id_order` = o.`id_order`
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON oh.`id_order_state` = os.`id_order_state`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
            WHERE oh.`id_order_history` = ' . (int) $this->id . ' AND (os.`send_email` = 1 OR os.`send_email_warehouse` = 1)');

        if (
            (!empty($result['template']) && Validate::isEmail($result['email']))
            || (!empty($result['warehouse_template']) && Validate::isEmail($result['email_warehouse']))
        ) {
            ShopUrl::cacheMainDomainForShop($order->id_shop);

            $topic = $result['osname'];
            $carrierUrl = '';
            if (Validate::isLoadedObject($carrier = new Carrier((int) $order->id_carrier, $order->id_lang))) {
                $carrierUrl = $carrier->url;
            }
            $uniqueReference = $order->getUniqReference();
            $data = [
                '{lastname}' => $result['lastname'],
                '{firstname}' => $result['firstname'],
                '{id_order}' => (int) $this->id_order,
                '{order_name}' => $uniqueReference,
                '{followup}' => str_replace('@', $order->getShippingNumber() ?? '', $carrierUrl),
                '{shipping_number}' => $order->getShippingNumber(),
            ];

            if ($result['module_name']) {
                $module = Module::getInstanceByName($result['module_name']);
                if (Validate::isLoadedObject($module)) {
                    if (isset($module->extra_mail_vars) && is_array($module->extra_mail_vars)) {
                        $data = array_merge($data, $module->extra_mail_vars);
                    }
                }
            }

            if (is_array($template_vars)) {
                $data = array_merge($data, $template_vars);
            }

            $context = Context::getContext();
            $data['{total_paid}'] = Tools::getContextLocale($context)->formatPrice((float) $order->total_paid, Currency::getIsoCodeById((int) $order->id_currency));

            if (Validate::isLoadedObject($order)) {
                // Attach invoice and / or delivery-slip if they exists and status is set to attach them
                if ($result['pdf_invoice'] || $result['pdf_delivery']) {
                    $currentLanguage = $context->language;
                    $orderLanguage = new Language((int) $order->id_lang);
                    $context->language = $orderLanguage;
                    $context->getTranslator()->setLocale($orderLanguage->locale);
                    $invoice = $order->getInvoicesCollection();
                    $file_attachement = [];

                    if ($result['pdf_invoice'] && (int) Configuration::get('PS_INVOICE') && $order->invoice_number) {
                        Hook::exec('actionPDFInvoiceRender', ['order_invoice_list' => $invoice]);
                        $pdf = new PDF($invoice, PDF::TEMPLATE_INVOICE, $context->smarty);
                        $file_attachement['invoice']['content'] = $pdf->render(false);
                        $file_attachement['invoice']['name'] = $pdf->getFilename();
                        $file_attachement['invoice']['mime'] = 'application/pdf';
                    }
                    if ($result['pdf_delivery'] && $order->delivery_number) {
                        $pdf = new PDF($invoice, PDF::TEMPLATE_DELIVERY_SLIP, $context->smarty);
                        $file_attachement['delivery']['content'] = $pdf->render(false);
                        $file_attachement['delivery']['name'] = $pdf->getFilename();
                        $file_attachement['delivery']['mime'] = 'application/pdf';
                    }

                    $context->language = $currentLanguage;
                    $context->getTranslator()->setLocale($currentLanguage->locale);
                } else {
                    $file_attachement = null;
                }

                $notify = (bool) $result['send_email'];
                $notify_result = !$notify;

                if ($notify && !empty($result['email']) && !empty($result['template'])) {
                    $notify_result = Mail::Send(
                        (int) $order->id_lang,
                        $result['template'],
                        $topic,
                        $data,
                        $result['email'],
                        $result['firstname'] . ' ' . $result['lastname'],
                        null,
                        null,
                        $file_attachement,
                        null,
                        _PS_MAIL_DIR_,
                        false,
                        (int) $order->id_shop
                    );
                }

                $notify_warehouse = (bool) $result['send_email_warehouse'];
                $notify_warehouse_result = !$notify_warehouse;

                if ($notify_warehouse && !empty($result['email_warehouse']) && !empty($result['warehouse_template'])) {
                    $topic .= ' ' . $uniqueReference;

                    $notify_warehouse_result = Mail::Send(
                        (int) $order->id_lang,
                        $result['warehouse_template'],
                        $topic,
                        $data,
                        $result['email_warehouse'],
                        'Warehouse',
                        null,
                        null,
                        $file_attachement,
                        null,
                        _PS_MAIL_DIR_,
                        false,
                        (int) $order->id_shop
                    );
                }

                return $notify_result && $notify_warehouse_result;
            }

            ShopUrl::resetMainDomainCache();
        }

        return true;
    }
}
