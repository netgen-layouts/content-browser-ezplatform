<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
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

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Provides the column value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return mixed
     */
    public function getValue(ItemInterface $item)
    {
        $tag = $item->getTag();

        if ($tag->id > 0) {
            return $this->tagsService->sudo(
                function (TagsService $tagsService) use ($tag) {
                    if (empty($tag->parentTagId)) {
                        return '(No parent)';
                    }

                    return $this->translationHelper->getTranslatedByMethod(
                        $tagsService->loadTag($tag->parentTagId),
                        'getKeyword'
                    );
                }
            );
        }

        return '';
    }
}
