<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class KrtvSingleSignOnIdentityProviderExtension
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\DependencyInjection
 */
class KrtvSingleSignOnIdentityProviderExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        // add config parameters to container
        $prefix = $this->getAlias() . '.';
        foreach ($config as $name => $value) {
            $container->setParameter($prefix . $name, $value);
        }

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->getDefinition('krtv_single_sign_on_identity_provider.manager.service_manager')
            ->replaceArgument(4, array(
                'service_parameter' => $container->getParameter($prefix . 'service_parameter'),
                'service_extra_parameter' => $container->getParameter($prefix . 'service_extra_parameter'),
                'target_path_parameter' => $container->getParameter($prefix . 'target_path_parameter'),
            ));

        // Set alias for ServiceManager
        $container->setAlias('sso_identity_provider.service_manager', new Alias('krtv_single_sign_on_identity_provider.manager.service_manager'));

        // Set alias for OTP
        $container->setAlias('sso_identity_provider.otp_manager', new Alias('krtv_single_sign_on_identity_provider.security.authentication.otp_manager.orm'));

        // Set alias for encoder
        $container->setAlias('sso_identity_provider.encoder', new Alias('krtv_single_sign_on_identity_provider.security.authentication.encoder'));

        // Set alias for uri_signer
        $container->setAlias('sso_identity_provider.uri_signer', new Alias('krtv_single_sign_on_identity_provider.uri_signer'));
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'krtv_single_sign_on_identity_provider';
    }
}
