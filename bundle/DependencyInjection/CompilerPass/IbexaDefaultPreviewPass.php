<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentBrowserIbexaBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IbexaDefaultPreviewPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ibexa.site_access.list')) {
            return;
        }

        $defaultRule = [
            'template' => $container->getParameter(
                'netgen_content_browser.ibexa.preview_template',
            ),
            'match' => [],
            'params' => [],
        ];

        /** @var string[] $siteAccessList */
        $siteAccessList = $container->getParameter('ibexa.site_access.list');
        $scopes = [...['default'], ...$siteAccessList];

        foreach ($scopes as $scope) {
            $scopeParams = [
                "ibexa.site_access.config.{$scope}.content_view",
                "ibexa.site_access.config.{$scope}.location_view",
            ];

            foreach ($scopeParams as $scopeParam) {
                if (!$container->hasParameter($scopeParam)) {
                    continue;
                }

                /** @var array<string, mixed[]>|null $scopeRules */
                $scopeRules = $container->getParameter($scopeParam);
                $scopeRules = $this->addDefaultPreviewRule($scopeRules, $defaultRule);
                $container->setParameter($scopeParam, $scopeRules);
            }
        }
    }

    /**
     * Adds the default Ibexa content preview template to default scope as a fallback
     * when no preview rules are defined.
     *
     * @param array<string, mixed[]>|null $scopeRules
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
