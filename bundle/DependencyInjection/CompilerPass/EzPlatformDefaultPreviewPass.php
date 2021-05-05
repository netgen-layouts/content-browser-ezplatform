<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserEzPlatformBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EzPlatformDefaultPreviewPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ezpublish.siteaccess.list')) {
            return;
        }

        $defaultRule = [
            'template' => $container->getParameter(
                'netgen_content_browser.ezplatform.preview_template',
            ),
            'match' => [],
            'params' => [],
        ];

        /** @var string[] $siteAccessList */
        $siteAccessList = $container->getParameter('ezpublish.siteaccess.list');
        $scopes = [...['default'], ...$siteAccessList];

        foreach ($scopes as $scope) {
            $scopeParams = [
                "ezsettings.{$scope}.content_view",
                "ezsettings.{$scope}.location_view",
            ];

            foreach ($scopeParams as $scopeParam) {
                if (!$container->hasParameter($scopeParam)) {
                    continue;
                }

                /** @var array<string, array>|null $scopeRules */
                $scopeRules = $container->getParameter($scopeParam);
                $scopeRules = $this->addDefaultPreviewRule($scopeRules, $defaultRule);
                $container->setParameter($scopeParam, $scopeRules);
            }
        }
    }

    /**
     * Adds the default eZ content preview template to default scope as a fallback
     * when no preview rules are defined.
     *
     * @param array<string, array>|null $scopeRules
     * @param array<string, mixed> $defaultRule
     *
     * @return array<string, mixed>
     */
    private function addDefaultPreviewRule(?array $scopeRules, array $defaultRule): array
    {
        $scopeRules = $scopeRules ?? [];
        $contentBrowserRules = $scopeRules['ngcb_preview'] ?? [];

        $contentBrowserRules += [
            '___ngcb_preview_default___' => $defaultRule,
        ];

        $scopeRules['ngcb_preview'] = $contentBrowserRules;

        return $scopeRules;
    }
}
