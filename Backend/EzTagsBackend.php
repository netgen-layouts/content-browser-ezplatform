<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\RootLocation;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;

class EzTagsBackend implements BackendInterface
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
     * @var array
     */
    protected $languages;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param array $languages
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper, array $languages)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
        $this->languages = $languages;
    }

    /**
     * Returns the default sections available in the backend.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface[]
     */
    public function getDefaultSections()
    {
        return array($this->loadLocation(0));
    }

    /**
     * Loads a  location by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface
     */
    public function loadLocation($id)
    {
        if (empty($id)) {
            return $this->buildItem($this->getRootTag());
        }

        return $this->loadItem($id);
    }

    /**
     * Loads the item by its value ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadItem($id)
    {
        try {
            $tag = $this->tagsService->loadTag($id);
        } catch (APINotFoundException $e) {
            throw new NotFoundException(
                sprintf(
                    'Item with "%s" ID not found.',
                    $id
                )
            );
        }

        return $this->buildItem($tag);
    }

    /**
     * Returns the locations below provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface[]
     */
    public function getSubLocations(LocationInterface $location)
    {
        $tags = $this->tagsService->loadTagChildren(
            $location->getTag()
        );

        return $this->buildItems($tags);
    }

    /**
     * Returns the count of locations below provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return int
     */
    public function getSubLocationsCount(LocationInterface $location)
    {
        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    /**
     * Returns the location items.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        $tags = $this->tagsService->loadTagChildren(
            $location->getTag()
        );

        return $this->buildItems($tags);
    }

    /**
     * Returns the location items count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\LocationInterface $location
     *
     * @return int
     */
    public function getSubItemsCount(LocationInterface $location)
    {
        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    /**
     * Searches for items.
     *
     * @param string $searchText
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
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

    /**
     * Returns the count of searched items.
     *
     * @param string $searchText
     *
     * @return int
     */
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
     * Builds the item from provided tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    protected function buildItem(Tag $tag)
    {
        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword'
        );

        if (empty($tag->id)) {
            return new RootLocation($tag, $tagName);
        }

        return new Item($tag, $tagName);
    }

    /**
     * Builds the items from provided tags.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    protected function buildItems(array $tags)
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
    protected function getRootTag()
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
