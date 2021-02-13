<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Ez\Item\EzTags\EzTagsInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use function in_array;

final class ParentTag implements ColumnValueProviderInterface
{
    private TagsService $tagsService;

    private TranslationHelper $translationHelper;

    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzTagsInterface) {
            return null;
        }

        return $this->tagsService->sudo(
            function (TagsService $tagsService) use ($item): string {
                if (in_array($item->getTag()->parentTagId, ['0', 0, null], true)) {
                    return '(No parent)';
                }

                return (string) $this->translationHelper->getTranslatedByMethod(
                    $tagsService->loadTag($item->getTag()->parentTagId),
                    'getKeyword'
                );
            }
        );
    }
}
