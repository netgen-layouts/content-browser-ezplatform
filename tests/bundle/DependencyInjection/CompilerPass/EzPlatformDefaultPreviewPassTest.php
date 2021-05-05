<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserEzPlatformBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass\EzPlatformDefaultPreviewPass;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

final class EzPlatformDefaultPreviewPassTest extends AbstractContainerBuilderTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->addCompilerPass(new EzPlatformDefaultPreviewPass());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass\EzPlatformDefaultPreviewPass::addDefaultPreviewRule
     * @covers \Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass\EzPlatformDefaultPreviewPass::process
     */
    public function testProcess(): void
    {
        $this->setParameter('ezpublish.siteaccess.list', ['cro', 'eng', 'admin']);
        $this->setParameter('netgen_content_browser.ezplatform.preview_template', 'template.html.twig');

        foreach (['default', 'cro', 'admin'] as $scope) {
            $this->setParameter(
                "ezsettings.{$scope}.content_view",
                ['full' => ['full_rule' => []]],
            );

            $this->setParameter(
                "ezsettings.{$scope}.location_view",
                ['ngcb_preview' => ['rule1' => [], 'rule2' => []]],
            );
        }

        $this->compile();

        foreach (['default', 'cro', 'admin'] as $scope) {
            $this->assertContainerBuilderHasParameter("ezsettings.{$scope}.content_view");
            $this->assertContainerBuilderHasParameter("ezsettings.{$scope}.location_view");

            /** @var array<string, mixed[]> $contentView */
            $contentView = $this->container->getParameter("ezsettings.{$scope}.content_view");
            self::assertArrayHasKey('ngcb_preview', $contentView);
            self::assertArrayHasKey('___ngcb_preview_default___', $contentView['ngcb_preview']);

            self::assertArrayHasKey('full', $contentView);
            self::assertArrayHasKey('full_rule', $contentView['full']);

            /** @var array<string, mixed[]> $locationView */
            $locationView = $this->container->getParameter("ezsettings.{$scope}.location_view");
            self::assertArrayHasKey('ngcb_preview', $locationView);
            self::assertArrayHasKey('___ngcb_preview_default___', $locationView['ngcb_preview']);

            self::assertArrayHasKey('rule1', $locationView['ngcb_preview']);
            self::assertArrayHasKey('rule2', $locationView['ngcb_preview']);
        }

        self::assertFalse($this->container->hasParameter('ezsettings.eng.content_view'));
        self::assertFalse($this->container->hasParameter('ezsettings.eng.location_view'));
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass\EzPlatformDefaultPreviewPass::process
     */
    public function testProcessWithEmptyContainer(): void
    {
        $this->compile();

        self::assertInstanceOf(FrozenParameterBag::class, $this->container->getParameterBag());
    }
}
