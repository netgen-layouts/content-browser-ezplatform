<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzTags;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use DateTime;

class ItemBuilder
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(
        TranslationHelper $translationHelper
    ) {
        $this->translationHelper = $translationHelper;
    }

    /**
     * Builds the browser item from tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item
     */
    public function buildItem(Tag $tag)
    {
        $pathString = $tag->pathString;
        if ($tag->id > 0) {
            $pathString = '/0' . $pathString;
        }

        $path = explode('/', trim($pathString, '/'));

        return new Item(
            $tag,
            array(
                'id' => $tag->id,
                'parentId' => $tag->parentTagId,
                'path' => array_map(function ($v) { return (int)$v; }, $path),
                'name' => $this->translationHelper->getTranslatedByMethod(
                    $tag,
                    'getKeyword'
                ),
                'isSelectable' => $tag->id > 0,
                'additionalColumns' => array(
                    'modified' => $tag->modificationDate->format(Datetime::ISO8601),
                    'published' => $tag->modificationDate->format(Datetime::ISO8601),
                )
            )
        );
    }
}
