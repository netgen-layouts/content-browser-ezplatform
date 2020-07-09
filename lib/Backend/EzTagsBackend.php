<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Backend;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Generator;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ez\Item\EzTags\EzTagsInterface;
use Netgen\ContentBrowser\Ez\Item\EzTags\Item;
use Netgen\ContentBrowser\Ez\Item\EzTags\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use function count;
use function in_array;
use function sprintf;

final class EzTagsBackend implements BackendInterface
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
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(
        TagsService $tagsService,
        TranslationHelper $translationHelper,
        ConfigResolverInterface $configResolver
    ) {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
        $this->configResolver = $configResolver;
    }

    public function getSections(): iterable
    {
        return [$this->loadLocation(0)];
    }

    public function loadLocation($id): LocationInterface
    {
        if (in_array($id, ['0', 0, null], true)) {
            return $this->buildLocation();
        }

        return $this->internalLoadItem($id);
    }

    public function loadItem($value): ItemInterface
    {
        return $this->internalLoadItem($value);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof EzTagsInterface) {
            return [];
        }

        $tags = $this->tagsService->loadTagChildren(
            $location->getTag()
        );

        return $this->buildItems($tags);
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if (!$location instanceof EzTagsInterface) {
            return 0;
        }

        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof EzTagsInterface) {
            return [];
        }

        $tags = $this->tagsService->loadTagChildren(
            $location->getTag(),
            $offset,
            $limit
        );

        return $this->buildItems($tags);
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof EzTagsInterface) {
            return 0;
        }

        return $this->tagsService->getTagChildrenCount(
            $location->getTag()
        );
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $searchQuery = new SearchQuery($searchText);
        $searchQuery->setOffset($offset);
        $searchQuery->setLimit($limit);

        $searchResult = $this->searchItems($searchQuery);

        return $searchResult->getResults();
    }

    public function searchCount(string $searchText): int
    {
        return $this->searchItemsCount(new SearchQuery($searchText));
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $languages = $this->configResolver->getParameter('languages');

        if (count($languages) === 0) {
            return new SearchResult();
        }

        $tags = $this->tagsService->loadTagsByKeyword(
            $searchQuery->getSearchText(),
            $languages[0],
            true,
            $searchQuery->getOffset(),
            $searchQuery->getLimit()
        );

        return new SearchResult($this->buildItems($tags));
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        $languages = $this->configResolver->getParameter('languages');

        if (count($languages) === 0) {
            return 0;
        }

        return $this->tagsService->getTagsByKeywordCount(
            $searchQuery->getSearchText(),
            $languages[0]
        );
    }

    /**
     * Builds the location.
     */
    private function buildLocation(): Location
    {
        $tag = $this->getRootTag();

        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword'
        );

        return new Location($tag, (string) $tagName);
    }

    /**
     * Returns the item for provided value.
     *
     * @param int|string $value
     */
    private function internalLoadItem($value): Item
    {
        try {
            $tag = $this->tagsService->loadTag((int) $value);
        } catch (APINotFoundException $e) {
            throw new NotFoundException(
                sprintf(
                    'Item with value "%s" not found.',
                    $value
                )
            );
        }

        return $this->buildItem($tag);
    }

    /**
     * Builds the item from provided tag.
     */
    private function buildItem(Tag $tag): Item
    {
        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword'
        );

        return new Item($tag, (string) $tagName);
    }

    /**
     * Builds the items from provided tags.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\TagList|\Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     *
     * @return \Generator<\Netgen\ContentBrowser\Ez\Item\EzTags\Item>
     */
    private function buildItems(iterable $tags): Generator
    {
        foreach ($tags as $tag) {
            yield $this->buildItem($tag);
        }
    }

    /**
     * Builds the root tag.
     */
    private function getRootTag(): Tag
    {
        return new Tag(
            [
                'id' => 0,
                'parentTagId' => null,
                'keywords' => [
                    'eng-GB' => 'All tags',
                ],
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
            ]
        );
    }
}
