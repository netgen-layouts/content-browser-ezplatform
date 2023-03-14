<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\NetgenTags;

use Ibexa\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\NetgenTagsInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

use function in_array;

final class ParentTag implements ColumnValueProviderInterface
{
    public function __construct(private TagsService $tagsService, private TranslationHelper $translationHelper)
    {
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof NetgenTagsInterface) {
            return null;
        }

        return $this->tagsService->sudo(
            function () use ($item): string {
                if (in_array($item->getTag()->parentTagId, ['0', 0, null], true)) {
                    return '(No parent)';
                }

                return (string) $this->translationHelper->getTranslatedByMethod(
                    $this->tagsService->loadTag($item->getTag()->parentTagId),
                    'getKeyword',
                );
            },
        );
    }
}
