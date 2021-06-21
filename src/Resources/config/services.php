<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use Mep\WebToolkitBundle\Admin\Field\Configurator\TranslatableFieldConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure(true)
        ->autowire(true);

    $services->set(TranslatableFieldConfigurator::class)
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR);
};