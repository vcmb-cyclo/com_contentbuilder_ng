<?php

/**
 * @package     Extension
 * @author      Xavier DANO
 * @link        
 * @copyright   Copyright (C) 2025 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Service;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use CB\Component\Contentbuilder\Administrator\Extension\ContentbuilderComponent;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new MVCFactory('CB\\Component\\Contentbuilder\\Administrator')
        );

        $container->registerServiceProvider(
            new ComponentDispatcherFactory('CB\\Component\\Contentbuilder\\Administrator')
        );

        $container->registerServiceProvider(
            new RouterFactory('CB\\Component\\Contentbuilder\\Administrator')
        );

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new ContentbuilderComponent(
                    $container->get(ComponentDispatcherFactory::class)
                );

                $component->setMVCFactory($container->get(MVCFactory::class));
                  return $component;
            }
        );
    }
};
