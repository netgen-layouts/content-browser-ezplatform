<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\TagsBundle\API\Repository\TagsService;

class EzTagsBackend implements BackendInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Returns the configured sections
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSections()
    {
        return array();
    }

    /**
     * Loads the item by its ID
     *
     * @param int|string $itemId
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadItem($itemId)
    {
        return $this->tagsService->loadTag($itemId);
    }

    /**
     * Returns the item children.
     *
     * @param int|string $itemId
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getChildren($itemId, array $params = array())
    {
        $tag = null;
        if ($itemId > 0) {
            $tag = $this->tagsService->loadTag($itemId);
        }

        return $this->tagsService->loadTagChildren($tag);
    }

    /**
     * Returns the item children count.
     *
     * @param int|string $itemId
     * @param array $params
     *
     * @return int
     */
    public function getChildrenCount($itemId, array $params = array())
    {
        $tag = null;
        if ($itemId > 0) {
            $tag = $this->tagsService->loadTag($itemId);
        }

        return $this->tagsService->getTagChildrenCount($tag);
    }

    /**
     * Searches for items.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function search($searchText, array $params = array())
    {
        return $this->tagsService->loadTagsByKeyword($searchText, 'eng-GB');
    }
}
