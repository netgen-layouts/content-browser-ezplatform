<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzTags;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\Bundle\ContentBrowserBundle\Item\Item;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Adapter\AdapterInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use DateTime;

class Adapter implements AdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzTags\ItemBuilder
     */
    protected $itemBuilder;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Netgen\Bundle\ContentBrowserBundle\Adapter\EzTags\ItemBuilder $itemBuilder
     */
    public function __construct(
        TagsService $tagsService,
        ItemBuilder $itemBuilder
    ) {
        $this->tagsService = $tagsService;
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * Returns all available columns and their names
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'modified' => 'netgen_content_browser.eztags.columns.modified',
            'published' => 'netgen_content_browser.eztags.columns.published',
        );
    }

    /**
     * Loads the item for provided ID.
     *
     * @param int|string $itemId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item with provided ID was not found
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\Item
     */
    public function loadItem($itemId)
    {
        // Tags have no root item, so we simulate it with item ID == 0
        if ($itemId == 0) {
            return $this->itemBuilder->buildItem(
                new Tag(
                    array(
                        'id' => 0,
                        'parentTagId' => null,
                        'pathString' => '/0/',
                        'modificationDate' => new DateTime("now"),
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => array(
                            'eng-GB' => 'Tags'
                        )
                    )
                )
            );
        }

        try {
            $tag = $this->tagsService->loadTag($itemId);
        } catch (APINotFoundException $e) {
            throw new NotFoundException("Item #{$itemId} not found.", 0, $e);
        }

        return $this->itemBuilder->buildItem($tag);
    }

    /**
     * Loads all children of the provided item.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\Item $item
     * @param string[] $types
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\Item[]
     */
    public function loadItemChildren(Item $item, array $types = array())
    {
        $tag = null;
        if ($item->id > 0) {
            $tag = $this->tagsService->loadTag($item->id);
        }

        $items = array_map(
            function (Tag $tag) {
                return $this->itemBuilder->buildItem(
                    $tag
                );
            },
            $this->tagsService->loadTagChildren($tag)
        );

        return $items;
    }

    /**
     * Returns true if provided item has children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\Item $item
     * @param string[] $types
     *
     * @return bool
     */
    public function hasChildren(Item $item, array $types = array())
    {
        $tag = null;
        if ($item->id > 0) {
            $tag = $this->tagsService->loadTag($item->id);
        }

        return $this->tagsService->getTagChildrenCount($tag) > 0;
    }
}
