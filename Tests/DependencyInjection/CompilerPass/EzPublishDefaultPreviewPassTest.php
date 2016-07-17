<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\ContentBrowserBundle\DependencyInjection\CompilerPass\EzPublishDefaultPreviewPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EzPublishDefaultPreviewPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EzPublishDefaultPreviewPass());
    }

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
            self::assertContainerBuilderHasParameter("ezsettings.{$scope}.content_view");
            self::assertContainerBuilderHasParameter("ezsettings.{$scope}.location_view");

            $contentView = $this->container->getParameter("ezsettings.{$scope}.content_view");
            self::assertArrayHasKey('ngcb_preview', $contentView);
            self::assertArrayHasKey('___ngcb_preview_default___', $contentView['ngcb_preview']);

            self::assertArrayHasKey('full', $contentView);
            self::assertArrayHasKey('full_rule', $contentView['full']);

            $locationView = $this->container->getParameter("ezsettings.{$scope}.location_view");
            self::assertArrayHasKey('ngcb_preview', $locationView);
            self::assertArrayHasKey('___ngcb_preview_default___', $locationView['ngcb_preview']);

            self::assertArrayHasKey('rule1', $locationView['ngcb_preview']);
            self::assertArrayHasKey('rule2', $locationView['ngcb_preview']);
        }

        self::assertFalse($this->container->hasParameter('ezsettings.eng.content_view'));
        self::assertFalse($this->container->hasParameter('ezsettings.eng.location_view'));
    }
}
