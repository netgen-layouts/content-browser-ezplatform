<?php

namespace Netgen\ContentBrowser\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Item\EzTags\Location;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class EzTagsBackend implements BackendInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    /**
     * @var array
     */
    private $languages = array();

    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Sets the current languages.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = is_array($languages) ? $languages : array();
    }

    public function getDefaultSections()
    {
        return array($this->loadLocation(0));
    }

    public function loadLocation($id)
    {
        if (empty($id)) {
            return $this->buildLocation();
        }

        return $this->loadItem($id);
    }

    public function loadItem($id)
    {
        try {
            $tag = $this->tagsService->loadTag($id);
        } catch (APINotFoundException $e) {
            throw new NotFoundException(
                sprintf(
                    'Item with ID %s not found.',
                    $id
                )
            );
        }

        return $this->buildItem($tag);
    }

    public function getSubLocations(LocationInterface $location)
    {
        $tags = $this->tagsService->loadTagChildren(
            $location->getTag()
        );

        return $this->buildItems($tags);
    }

    public function getSubLocationsCount(LocationInterface $location)
    {
        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        $tags = $this->tagsService->loadTagChildren(
            $location->getTag(),
            $offset,
            $limit
        );

        return $this->buildItems($tags);
    }

    public function getSubItemsCount(LocationInterface $location)
    {
        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    public function search($searchText, $offset = 0, $limit = 25)
    {
        if (empty($this->languages)) {
            return array();
        }

        $tags = $this->tagsService->loadTagsByKeyword(
            $searchText,
            $this->languages[0],
            true,
            $offset,
            $limit
        );

        return $this->buildItems($tags);
    }

    public function searchCount($searchText)
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
     * Builds the location.
     *
     * @return \Netgen\ContentBrowser\Item\EzTags\Location
     */
    private function buildLocation()
    {
        $tag = $this->getRootTag();

        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword'
        );

        return new Location($tag, $tagName);
    }

    /**
     * Builds the item from provided tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Netgen\ContentBrowser\Item\EzTags\Item
     */
    private function buildItem(Tag $tag)
    {
        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword'
        );

        return new Item($tag, $tagName);
    }

    /**
     * Builds the items from provided tags.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     *
     * @return \Netgen\ContentBrowser\Item\EzTags\Item[]
     */
    private function buildItems(array $tags)
    {
        return array_map(
            function (Tag $tag) {
                return $this->buildItem($tag);
            },
            $tags
        );
    }

    /**
     * Builds the root tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private function getRootTag()
    {
        return new Tag(
            array(
                'id' => 0,
                'parentTagId' => null,
                'keywords' => array(
                    'eng-GB' => 'All tags',
                ),
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
            )
        );
    }
}
