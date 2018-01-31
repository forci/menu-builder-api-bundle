<?php

namespace Forci\Bundle\MenuBuilderApi\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ForciMenuBuilderApiExtension extends Extension {

    public function load(array $configs, ContainerBuilder $container) {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $bag = $container->getParameterBag();

        $bag->set('forci_menu_builder_api.config', $config);

        $bag->set('forci_menu_builder_api.secret', $config['secret']);
    }

    public function getXsdValidationBasePath() {
        return __DIR__ . '/../Resources/config/';
    }

    public function getNamespace() {
        return 'http://www.example.com/symfony/schema/';
    }

}