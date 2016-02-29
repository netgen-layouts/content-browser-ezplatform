<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\DependencyInjection;

use Netgen\Bundle\ContentBrowserBundle\DependencyInjection\NetgenContentBrowserExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class NetgenContentBrowserExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Return an array of container extensions that need to be registered for
     * each test (usually just the container extension you are testing).
     *
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array(
            new NetgenContentBrowserExtension(),
        );
    }

    /**
     * Optionally override this method to return an array that will be used as the minimal configuration for loading
     * the container extension under test, to prevent a test from failing because of a missing required
     * configuration value for the container extension.
     *
     * @return array
     */
    protected function getMinimalConfiguration()
    {
        return array(
            'trees' => array(
                'default' => array(
                    'root_locations' => array(42),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\DependencyInjection\NetgenContentBrowserExtension::load
     */
    public function testParameters()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('netgen_content_browser.trees');

        self::assertEquals(
            array(
                'default' => array(
                    'root_locations' => array(42),
                    'min_selected' => 1,
                    'max_selected' => 0,
                    'location_template' => 'NetgenContentBrowserBundle:ezpublish:location.html.twig',
                    'default_columns' => array('name', 'type', 'visible'),
                ),
            ),
            $this->container->getParameter('netgen_content_browser.trees')
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\DependencyInjection\NetgenContentBrowserExtension::load
     */
    public function testServices()
    {
        $this->load();

        $this->assertContainerBuilderHasService('netgen_content_browser.controller.api.tree');
        $this->assertContainerBuilderHasService('netgen_content_browser.adapter.ezpublish');
        $this->assertContainerBuilderHasService('netgen_content_browser.repository');

        $this->assertContainerBuilderHasAlias(
            'netgen_content_browser.adapter',
            'netgen_content_browser.adapter.ezpublish'
        );
    }
}
