<?php

/**
 * Order Features
 *
 * PHP version 7.4
 *
 * @category  Module
 * @package   PrestaShop
 * @author    ChillCode https://github.com/chillcode
 * @copyright 2003-2023
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * @version   GIT: 1.0.2
 * @link      https://github.com/chillcode
 */

defined('_PS_VERSION_') || exit;

class Ps_Orderfeatures extends Module
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->name = 'ps_orderfeatures';
        $this->tab = 'content_management';
        $this->version = '1.0.2';
        $this->author = 'Chillcode';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.8.0', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName            = $this->trans(
            'Order Features for PrestaShop',
            array(),
            'Modules.Orderfeatures.Admin'
        );

        $this->description            = $this->trans(
            'Order Features for PrestaShop',
            array(),
            'Modules.Orderfeatures.Admin'
        );

        $this->confirmUninstall       = $this->trans(
            'Are you sure you want to uninstall Order Features for PrestaShop?',
            array(),
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
