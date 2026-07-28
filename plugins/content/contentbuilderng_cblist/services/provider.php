<?php

\defined('_JEXEC') or die;

use CB\Plugin\Content\ContentbuilderngList\Extension\ContentbuilderngList;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): ContentbuilderngList {
                $plugin = new ContentbuilderngList(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('content', 'contentbuilderng_cblist')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
