<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Value;

class EzLocationBackend implements BackendInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var string[]
     */
    protected $categoryContentTypes;

    /**
     * @var int[]
     */
    protected $defaultSections;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param string[] $categoryContentTypes
     * @param int[] $defaultSections
     */
    public function __construct(
        SearchService $searchService,
        TranslationHelper $translationHelper,
        array $categoryContentTypes,
        array $defaultSections
    ) {
        $this->searchService = $searchService;
        $this->translationHelper = $translationHelper;
        $this->categoryContentTypes = $categoryContentTypes;
        $this->defaultSections = $defaultSections;
    }

    /**
     * Returns the default sections available in the backend.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface[]
     */
    public function getDefaultSections()
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($this->defaultSections);

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
    }

    /**
     * Loads a  category by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If category does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface
     */
    public function loadCategory($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->searchService->findLocations($query);

        if (!empty($result->searchHits)) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Item with "%s" ID not found.',
                $id
            )
        );
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
        return $this->loadCategory($id);
    }

    /**
     * Returns the categories below provided category.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface $category
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface[]
     */
    public function getSubCategories(CategoryInterface $category)
    {
        $criteria = array(
            new Criterion\ParentLocationId($category->getId()),
            new Criterion\ContentTypeIdentifier($this->categoryContentTypes),
        );

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
    }

    /**
     * Returns the count of categories below provided category.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface $category
     *
     * @return int
     */
    public function getSubCategoriesCount(CategoryInterface $category)
    {
        $criteria = array(
            new Criterion\ParentLocationId($category->getId()),
            new Criterion\ContentTypeIdentifier($this->categoryContentTypes),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Returns the category items.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface $category
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubItems(CategoryInterface $category, $offset = 0, $limit = 25)
    {
        $criteria = array(
            new Criterion\ParentLocationId($category->getId()),
        );

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
    }

    /**
     * Returns the category items count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface $category
     *
     * @return int
     */
    public function getSubItemsCount(CategoryInterface $category)
    {
        $criteria = array(
            new Criterion\ParentLocationId($category->getId()),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
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
        $query = new LocationQuery();

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->offset = $offset;
        $query->limit = $limit;

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
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
        $query = new LocationQuery();

        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\FullText($searchText),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Builds the item from provided search hit.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $searchHit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    protected function buildItem(SearchHit $searchHit)
    {
        return new Item(
            new Value(
                $searchHit->valueObject,
                $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $searchHit->valueObject->contentInfo
                )
            )
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    protected function buildItems(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $this->buildItem($searchHit);
            },
            $searchResult->searchHits
        );
    }
}
