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

namespace PrestaShop\Module\OrderFeatures\Form\Admin\Configure\ShopParameters\OrderStates;

use PrestaShop\PrestaShop\Core\Domain\Configuration\ShopConfigurationInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCatalogInterface;
use PrestaShopBundle\Form\Admin\Configure\ShopParameters\OrderStates\OrderStateType as BaseOrderStateType;
use PrestaShopBundle\Form\Admin\Type\TranslatableChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderStateType extends BaseOrderStateType
{
    private BaseOrderStateType $inner;
    private TranslatorInterface $translator;

    /**
     * @var array
     */
    private $warehouseTemplates;

    /**
     * @var array
     */
    private $warehouseTemplateAttributes;

    public function __construct(
        BaseOrderStateType $inner,
        TranslatorInterface $translator,
        array $locales,
        ThemeCatalogInterface $themeCatalog,
        UrlGeneratorInterface $routing,
        ShopConfigurationInterface $configuration,
    ) {
        parent::__construct($translator, $locales, $themeCatalog, $routing, $configuration);
        $this->inner = $inner;
        $this->translator = $translator;

        $mailTheme = $configuration->get('PS_MAIL_THEME', 'modern');

        $mailLayouts = $themeCatalog->getByName($mailTheme)->getLayouts();

        foreach ($locales as $locale) {
            $languageId = $locale['id_lang'];
            $this->warehouseTemplates[$languageId] = $this->warehouseTemplateAttributes[$languageId] = [];

            /** @var Layout $mailLayout */
            foreach ($mailLayouts as $mailLayout) {
                $this->warehouseTemplates[$languageId][$mailLayout->getName()] = $mailLayout->getName();
                $this->warehouseTemplateAttributes[$languageId][$mailLayout->getName()] = [
                    'data-preview' => $routing->generate(
                        empty($mailLayout->getModuleName()) ?
                            'admin_mail_theme_preview_layout' :
                            'admin_mail_theme_preview_module_layout',
                        [
                            'theme' => $mailTheme,
                            'layout' => $mailLayout->getName(),
                            'type' => 'html',
                            'locale' => $locale['iso_code'],
                            'module' => $mailLayout->getModuleName(),
                        ]
                    ),
                ];
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('send_email_warehouse', CheckboxType::class, [
                'required' => false,
                'label' => 'Send email to warehouse',
                'attr' => [
                    'material_design' => true,
                ],
            ])
            ->add('email_warehouse', EmailType::class, [
                'required' => false,
                'label' => 'Warehouse email',
                'constraints' => [
                    new Email([
                        'message' => $this->translator->trans(
                            'The email address is invalid.',
                            [],
                            'Admin.Notifications.Error'
                        ),
                    ]),
                ],
                'row_attr' => ['class' => 'order_state_warehouse_email_select'],
                'attr' => [
                    'data-dependency' => 'send_email_warehouse',
                ],
            ])->add('warehouse_template', TranslatableChoiceType::class, [
                'label' => $this->trans('Template', 'Admin.Shopparameters.Feature'),
                'hint' => sprintf(
                    '%s<br>%s',
                    $this->trans('Only letters, numbers and underscores ("_") are allowed.', 'Admin.Shopparameters.Help'),
                    $this->trans('Email template for both .html and .txt.', 'Admin.Shopparameters.Help')
                ),
                'required' => false,
                'choices' => $this->warehouseTemplates,
                'row_attr' => $this->warehouseTemplateAttributes + [
                    'class' => 'order_state_warehouse_template_select',
                ],
                'button' => [
                    'label' => $this->trans('Preview', 'Admin.Actions'),
                    'icon' => 'visibility',
                    'class' => 'btn btn-primary',
                    'id' => 'order_state_warehouse_template_preview',
                ],
            ]);
    }
}
