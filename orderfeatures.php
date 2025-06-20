<?php

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
 * @version GIT: 2.0.0
 *
 * @see https://github.com/chillcode
 */
defined('_PS_VERSION_') || exit;

class Orderfeatures extends Module
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->name = 'orderfeatures';
        $this->tab = 'content_management';
        $this->version = '2.0.0';
        $this->author = 'Chillcode';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0.0', 'max' => _PS_VERSION_];

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
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
