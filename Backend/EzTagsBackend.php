<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\TagsBundle\API\Repository\TagsService;

class EzTagsBackend implements BackendInterface
{
    protected $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function getSections()
    {
        return array();
    }

    public function loadItem($itemId)
    {
        return $this->tagsService->loadTag($itemId);
    }

    public function getChildren(array $params = array())
    {
        $tag = null;
        if ($params['item_id'] > 0) {
            $tag = $this->tagsService->loadTag($params['item_id']);
        }

        return $this->tagsService->loadTagChildren($tag);
    }

    public function getChildrenCount(array $params = array())
    {
        $tag = null;
        if ($params['item_id'] > 0) {
            $tag = $this->tagsService->loadTag($params['item_id']);
        }

        return $this->tagsService->getTagChildrenCount($tag);
    }

    public function search(array $params = array())
    {
        return $this->tagsService->loadTagsByKeyword($params['search_text'], 'eng-GB');
    }
}
