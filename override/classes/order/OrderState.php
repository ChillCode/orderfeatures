<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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
class OrderState extends OrderStateCore
{
    /**
     * @var bool
     */
    public $send_email_warehouse;

    /**
     * @var string
     */
    public $email_warehouse;

    /**
     * @var string|array<int, string>
     */
    public $warehouse_template;

    public function __construct(
        $id = null,
        $id_lang = null,
        $id_shop = null,
    ) {
        self::$definition['fields']['send_email_warehouse'] = [
            'type' => self::TYPE_BOOL,
        ];
        self::$definition['fields']['email_warehouse'] = [
            'type' => self::TYPE_STRING,
            'size' => 255,
        ];

        self::$definition['fields']['warehouse_template'] = [
            'type' => self::TYPE_STRING,
            'lang' => true,
            'validate' => 'isTplName',
            'size' => 64,
        ];

        parent::__construct($id, $id_lang, $id_shop);
    }
}
