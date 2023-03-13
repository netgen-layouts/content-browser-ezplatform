<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserIbexaBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Netgen\Bundle\ContentBrowserIbexaBundle\DependencyInjection\CompilerPass\IbexaDefaultPreviewPass;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

#[CoversClass(IbexaDefaultPreviewPass::class)]
final class IbexaDefaultPreviewPassTest extends AbstractContainerBuilderTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->addCompilerPass(new IbexaDefaultPreviewPass());
    }

    public function testProcess(): void
    {
        $this->setParameter('ibexa.site_access.list', ['cro', 'eng', 'admin']);
        $this->setParameter('netgen_content_browser.ibexa.preview_template', 'template.html.twig');

        foreach (['default', 'cro', 'admin'] as $scope) {
            $this->setParameter(
                "ibexa.site_access.config.{$scope}.content_view",
                ['full' => ['full_rule' => []]],
            );

            $this->setParameter(
                "ibexa.site_access.config.{$scope}.location_view",
                ['ngcb_preview' => ['rule1' => [], 'rule2' => []]],
            );
        }

        $this->compile();

        foreach (['default', 'cro', 'admin'] as $scope) {
            $this->assertContainerBuilderHasParameter("ibexa.site_access.config.{$scope}.content_view");
            $this->assertContainerBuilderHasParameter("ibexa.site_access.config.{$scope}.location_view");

            /** @var array<string, mixed[]> $contentView */
            $contentView = $this->container->getParameter("ibexa.site_access.config.{$scope}.content_view");
            self::assertArrayHasKey('ngcb_preview', $contentView);
            self::assertArrayHasKey('___ngcb_preview_default___', $contentView['ngcb_preview']);

            self::assertArrayHasKey('full', $contentView);
            self::assertArrayHasKey('full_rule', $contentView['full']);

            /** @var array<string, mixed[]> $locationView */
            $locationView = $this->container->getParameter("ibexa.site_access.config.{$scope}.location_view");
            self::assertArrayHasKey('ngcb_preview', $locationView);
            self::assertArrayHasKey('___ngcb_preview_default___', $locationView['ngcb_preview']);

            self::assertArrayHasKey('rule1', $locationView['ngcb_preview']);
            self::assertArrayHasKey('rule2', $locationView['ngcb_preview']);
        }

        self::assertFalse($this->container->hasParameter('ibexa.site_access.config.eng.content_view'));
        self::assertFalse($this->container->hasParameter('ibexa.site_access.config.eng.location_view'));
    }

    public function testProcessWithEmptyContainer(): void
    {
        $this->compile();

        self::assertInstanceOf(FrozenParameterBag::class, $this->container->getParameterBag());
    }
}
