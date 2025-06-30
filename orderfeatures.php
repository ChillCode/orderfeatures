<?php

//phpcs:disable PSR12.Files.FileHeader.SpacingAfterBlock, PSR1.Classes.ClassDeclaration.MissingNamespace, PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Order Features
 *
 * PHP version 8.1
 *
 * @category Module
 *
 * @author ChillCode https://github.com/chillcode
 * @copyright 2003-2023
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *
 * @version GIT: 3.0.0
 *
 * @see https://github.com/chillcode
 */
defined('_PS_VERSION_') || exit;

define('ORDERFEATURES_VERSION', '3.0.0');

define('ORDERFEATURES_ADD', 1);
define('ORDERFEATURES_DROP', 2);
define('ORDERFEATURES_IGNORE', 3);

class Orderfeatures extends Module
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->name = 'orderfeatures';
        $this->tab = 'content_management';
        $this->version = '3.0.0';
        $this->author = 'Chillcode';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans(
            'Order Features for PrestaShop',
            [],
            'Modules.Orderfeatures.Admin'
        );

        $this->description = $this->trans(
            'Order Features for PrestaShop',
            [],
            'Modules.Orderfeatures.Admin'
        );

        $this->confirmUninstall = $this->trans(
            'Are you sure you want to uninstall Order Features for PrestaShop?',
            [],
            'Modules.Orderfeatures.Admin'
        );
    }

    public function install()
    {
        return $this->alterTables(ORDERFEATURES_ADD) && parent::install() && $this->registerHook('actionAdminControllerSetMedia');
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        return $this->alterTables(ORDERFEATURES_DROP) && parent::uninstall();
    }
    public function hookActionAdminControllerSetMedia()
    {
        $controller = Tools::getValue('controller');
        if ($controller === 'AdminStatuses') {
            $this->context->controller->addJS(
                $this->_path . 'views/js/order_states_form.bundle-extension.js'
            );
        }
    }

    /**
     * Alter Core Tables to easy add the new Options, deleted on module uninstall.
     *
     * TODO: Use doctrine instead of ObjectModel.
     *
     * @param int $action available options are ADD, DROP or IGNORE
     *
     * @return bool
     *
     * @throws Exception
     */
    private function alterTables($action = ORDERFEATURES_ADD): bool
    {
        if ($action === ORDERFEATURES_IGNORE) {
            return true;
        }

        $db = Db::getInstance();
        $table = _DB_PREFIX_ . 'order_state';

        // Schema changes
        $schemaChanges = [
            'order_state' => [
                'send_email_warehouse' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER send_email',
                'email_warehouse' => "VARCHAR(255) NOT NULL DEFAULT '' AFTER send_email_warehouse",
            ],
            'order_state_lang' => [
                'warehouse_template' => 'VARCHAR(64) NOT NULL AFTER template',
            ],
        ];

        $alterTables = true;

        foreach ($schemaChanges as $table => $columns) {
            $fullTable = _DB_PREFIX_ . $table;

            foreach ($columns as $column => $definition) {
                switch ($action) {
                    case ORDERFEATURES_ADD:
                        if (!$this->columnExists($fullTable, $column)) {
                            $sql = "ALTER TABLE `$fullTable` ADD COLUMN `$column` $definition";
                            $alterTables &= $db->execute($sql, false);
                        }
                        break;

                    case ORDERFEATURES_DROP:
                        if ($this->columnExists($fullTable, $column)) {
                            $sql = "ALTER TABLE `$fullTable` DROP COLUMN `$column`";
                            $alterTables &= $db->execute($sql, false);
                        }
                        break;
                }
            }
        }

        return (bool) $alterTables;
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) !empty(Db::getInstance()->executeS("SHOW COLUMNS FROM `$table` LIKE '$column'"));
    }
}
