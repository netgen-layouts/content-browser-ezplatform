<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzTags\EzTagsInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

final class ParentTag implements ColumnValueProviderInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzTagsInterface) {
            return null;
        }

        return $this->tagsService->sudo(
            function (TagsService $tagsService) use ($item) {
                if (empty($item->getTag()->parentTagId)) {
                    return '(No parent)';
                }

                return $this->translationHelper->getTranslatedByMethod(
                    $tagsService->loadTag($item->getTag()->parentTagId),
                    'getKeyword'
                );
            }
        );
    }
}
