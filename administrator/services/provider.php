<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */
// admin/services/provider.php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // Enregistre la MVC Factory avec votre namespace namespacé
        $container->registerServiceProvider(new MVCFactory('\\CB\\Component\\Contentbuilder\\Administrator'));

        // Enregistre le Dispatcher Factory (indispensable pour sortir du mode legacy !)
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\CB\\Component\\Contentbuilder\\Administrator'));

        // Enregistre l'instance du composant (MVCComponent fait le job pour la plupart des cas)
        $container->set(
            ComponentInterface::class,
            function (Container $container): ComponentInterface
            {
                $component = new MVCComponent(
                    $container->get('Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface'),
                    $container->get(MVCFactoryInterface::class)
                );

                return $component;
            }
        );
    }
};