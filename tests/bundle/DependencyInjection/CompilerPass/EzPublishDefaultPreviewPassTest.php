<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\ContentBrowserBundle\DependencyInjection\CompilerPass\EzPublishDefaultPreviewPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EzPublishDefaultPreviewPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\DependencyInjection\CompilerPass\EzPublishDefaultPreviewPass::process
     * @covers \Netgen\Bundle\ContentBrowserBundle\DependencyInjection\CompilerPass\EzPublishDefaultPreviewPass::addDefaultPreviewRule
     */
    public function testProcess()
    {
        $this->setParameter('ezpublish.siteaccess.list', array('cro', 'eng', 'admin'));
        $this->setParameter('netgen_content_browser.ezpublish.default_preview_template', 'template.html.twig');

        foreach (array('default', 'cro', 'admin') as $scope) {
            $this->setParameter(
                "ezsettings.{$scope}.content_view",
                array('full' => array('full_rule' => array()))
            );

            $this->setParameter(
                "ezsettings.{$scope}.location_view",
                array('ngcb_preview' => array('rule1' => array(), 'rule2' => array()))
            );
        }

        $this->compile();

        foreach (array('default', 'cro', 'admin') as $scope) {
            $this->assertContainerBuilderHasParameter("ezsettings.{$scope}.content_view");
            $this->assertContainerBuilderHasParameter("ezsettings.{$scope}.location_view");

            $contentView = $this->container->getParameter("ezsettings.{$scope}.content_view");
            $this->assertArrayHasKey('ngcb_preview', $contentView);
            $this->assertArrayHasKey('___ngcb_preview_default___', $contentView['ngcb_preview']);

            $this->assertArrayHasKey('full', $contentView);
            $this->assertArrayHasKey('full_rule', $contentView['full']);

            $locationView = $this->container->getParameter("ezsettings.{$scope}.location_view");
            $this->assertArrayHasKey('ngcb_preview', $locationView);
            $this->assertArrayHasKey('___ngcb_preview_default___', $locationView['ngcb_preview']);

            $this->assertArrayHasKey('rule1', $locationView['ngcb_preview']);
            $this->assertArrayHasKey('rule2', $locationView['ngcb_preview']);
        }

        $this->assertFalse($this->container->hasParameter('ezsettings.eng.content_view'));
        $this->assertFalse($this->container->hasParameter('ezsettings.eng.location_view'));
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\DependencyInjection\CompilerPass\EzPublishDefaultPreviewPass::process
     * @doesNotPerformAssertions
     */
    public function testProcessWithEmptyContainer()
    {
        $this->compile();
    }

    /**
     * Register the compiler pass under test.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EzPublishDefaultPreviewPass());
    }
}
