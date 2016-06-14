<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class EzTagsBackend implements BackendInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface
     */
    protected $valueLoader;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $languages;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface $valueLoader
     * @param array $config
     * @param array $languages
     */
    public function __construct(TagsService $tagsService, ValueLoaderInterface $valueLoader, array $config, array $languages)
    {
        $this->tagsService = $tagsService;
        $this->valueLoader = $valueLoader;
        $this->config = $config;
        $this->languages = $languages;
    }

    /**
     * Returns the value type this backend supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'eztags';
    }

    /**
     * Returns the value children.
     *
     * @param int|string $valueId
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface[]
     */
    public function getChildren($valueId, array $params = array())
    {
        $offset = 0;
        $limit = -1;

        if (isset($params['offset']) || isset($params['limit'])) {
            $offset = !empty($params['offset']) ? $params['offset'] : 0;
            $limit = !empty($params['limit']) ? $params['limit'] : $this->config['default_limit'];
        }

        $tags = $this->tagsService->loadTagChildren(
            !empty($valueId) ?
                $this->tagsService->loadTag($valueId) :
                null,
            $offset,
            $limit
        );

        return $this->buildValues($tags);
    }

    /**
     * Returns the value children count.
     *
     * @param int|string $valueId
     * @param array $params
     *
     * @return int
     */
    public function getChildrenCount($valueId, array $params = array())
    {
        return $this->tagsService->getTagChildrenCount(
            !empty($valueId) ?
                $this->tagsService->loadTag($valueId) :
                null
        );
    }

    /**
     * Searches for values.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface[]
     */
    public function search($searchText, array $params = array())
    {
        if (empty($this->languages)) {
            return array();
        }

        $tags = $this->tagsService->loadTagsByKeyword(
            $searchText,
            $this->languages[0],
            true,
            !empty($params['offset']) ? $params['offset'] : 0,
            !empty($params['limit']) ? $params['limit'] : $this->config['default_limit']
        );

        return $this->buildValues($tags);
    }

    /**
     * Returns the count of searched values.
     *
     * @param string $searchText
     * @param array $params
     *
     * @return int
     */
    public function searchCount($searchText, array $params = array())
    {
        if (empty($this->languages)) {
            return 0;
        }

        return $this->tagsService->getTagsByKeywordCount(
            $searchText,
            $this->languages[0]
        );
    }

    /**
     * Builds the values from tags.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     *
     * @return array
     */
    protected function buildValues(array $tags)
    {
        return array_map(
            function (Tag $tag) {
                return $this->valueLoader->buildValue($tag);
            },
            $tags
        );
    }
}
