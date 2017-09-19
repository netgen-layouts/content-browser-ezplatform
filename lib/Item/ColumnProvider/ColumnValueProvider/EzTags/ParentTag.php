<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

class ParentTag implements ColumnValueProviderInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    public function getValue(ItemInterface $item)
    {
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
