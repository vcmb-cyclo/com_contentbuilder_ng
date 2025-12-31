<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */
// administrator/services/provider.php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\CB\\Component\\Contentbuilder'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\CB\\Component\\Contentbuilder'));

        $container->set(
            ComponentInterface::class,
            function (Container $container): ComponentInterface
            {
                // ✅ 1) On construit le composant avec le DispatcherFactory
                $component = new MVCComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );

                // ✅ 2) Puis on injecte la MVCFactory explicitement
                $component->setMVCFactory(
                    $container->get(MVCFactoryInterface::class)
                );

                return $component;
            }
        );
    }
};
