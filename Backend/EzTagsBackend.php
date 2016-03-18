<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class EzTagsBackend implements BackendInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param array $config
     */
    public function __construct(TagsService $tagsService, array $config)
    {
        $this->tagsService = $tagsService;
        $this->config = $config;
    }

    /**
     * Returns the configured sections.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSections()
    {
        $sections = array();
        foreach ($this->config['root_items'] as $rootItemId) {
            $sections[] = $this->loadItem($rootItemId);
        }

        return $sections;
    }

    /**
     * Loads the item by its ID.
     *
     * @param int|string $itemId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadItem($itemId)
    {
        if ($itemId > 0) {
            return $this->tagsService->loadTag($itemId);
        }

        return new Tag(
            array(
                'id' => 0,
                'keywords' => array(
                    'eng-GB' => 'All tags'
                ),
                'mainLanguageCode' => 'eng-GB'
            )
        );
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
