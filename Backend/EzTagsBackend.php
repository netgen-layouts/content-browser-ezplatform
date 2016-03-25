<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
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
            try {
                $sections[] = $this->loadItem($rootItemId);
            } catch (NotFoundException $e) {
                // Do nothing
            }
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
            try {
                return $this->tagsService->loadTag($itemId);
            } catch (APINotFoundException $e) {
                throw new NotFoundException("Tag with ID {$itemId} not found.");
            }
        }

        return new Tag(
            array(
                'id' => 0,
                'keywords' => array(
                    'eng-GB' => 'All tags',
                ),
                'mainLanguageCode' => 'eng-GB',
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
        return $this->tagsService->loadTagChildren(
            !empty($itemId) ?
                $this->tagsService->loadTag($itemId) :
                null,
            !empty($params['offset']) ? $params['offset'] : 0,
            !empty($params['limit']) ? $params['limit'] : $this->config['default_limit']
        );
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
        return $this->tagsService->getTagChildrenCount(
            !empty($itemId) ?
                $this->tagsService->loadTag($itemId) :
                null
        );
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
        return $this->tagsService->loadTagsByKeyword(
            $searchText,
            'eng-GB',
            true,
            !empty($params['offset']) ? $params['offset'] : 0,
            !empty($params['limit']) ? $params['limit'] : $this->config['default_limit']
        );
    }

    /**
     * Returns the count of searched items.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return int
     */
    public function searchCount($searchText, array $params = array())
    {
        return $this->tagsService->getTagsByKeywordCount(
            $searchText,
            'eng-GB',
            true
        );
    }
}
