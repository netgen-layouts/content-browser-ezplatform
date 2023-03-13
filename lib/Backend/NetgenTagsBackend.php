<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Backend;

use DateTimeImmutable;
use Generator;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as APINotFoundException;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item;
use Netgen\ContentBrowser\Ibexa\Item\NetgenTags\NetgenTagsInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagList;

use function count;
use function sprintf;

final class NetgenTagsBackend implements BackendInterface
{
    public function __construct(
        private TagsService $tagsService,
        private TranslationHelper $translationHelper,
        private ConfigResolverInterface $configResolver,
    ) {
    }

    public function getSections(): iterable
    {
        yield $this->buildRootItem();
    }

    public function loadLocation($id): Item
    {
        if ((int) $id === 0) {
            return $this->buildRootItem();
        }

        return $this->internalLoadItem((int) $id);
    }

    public function loadItem($value): Item
    {
        return $this->internalLoadItem((int) $value);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof NetgenTagsInterface) {
            return [];
        }

        $tags = $this->tagsService->loadTagChildren(
            $location->getTag(),
        );

        return $this->buildItems($tags);
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if (!$location instanceof NetgenTagsInterface) {
            return 0;
        }

        return $this->tagsService->getTagChildrenCount(
            $location->getTag(),
        );
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof NetgenTagsInterface) {
            return [];
        }

        $tags = $this->tagsService->loadTagChildren(
            $location->getTag(),
            $offset,
            $limit,
        );

        return $this->buildItems($tags);
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof NetgenTagsInterface) {
            return 0;
        }

        return $this->tagsService->getTagChildrenCount(
            $location->getTag(),
        );
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $searchQuery = new SearchQuery($searchText);
        $searchQuery->setOffset($offset);
        $searchQuery->setLimit($limit);

        return $this->searchItems($searchQuery)->getResults();
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
            $searchQuery->getLimit(),
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
            $languages[0],
        );
    }

    /**
     * Builds the root item.
     */
    private function buildRootItem(): Item
    {
        $tag = $this->getRootTag();

        $tagName = $this->translationHelper->getTranslatedByMethod(
            $tag,
            'getKeyword',
        );

        return new Item($tag, (string) $tagName);
    }

    /**
     * Returns the item for provided value.
     */
    private function internalLoadItem(int $value): Item
    {
        try {
            $tag = $this->tagsService->loadTag($value);
        } catch (APINotFoundException) {
            throw new NotFoundException(
                sprintf(
                    'Item with value "%s" not found.',
                    $value,
                ),
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
            'getKeyword',
        );

        return new Item($tag, (string) $tagName);
    }

    /**
     * Builds the items from provided tags.
     *
     * @return \Generator<\Netgen\ContentBrowser\Ibexa\Item\NetgenTags\Item>
     */
    private function buildItems(TagList $tags): Generator
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
                'parentTagId' => 0,
                'keywords' => [
                    'eng-GB' => 'All tags',
                ],
                'mainLanguageCode' => 'eng-GB',
                'alwaysAvailable' => true,
                'modificationDate' => new DateTimeImmutable(),
            ],
        );
    }
}
