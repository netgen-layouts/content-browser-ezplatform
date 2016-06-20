<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Item;
use Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Value;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

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
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param string[] $categoryContentTypes
     */
    public function __construct(SearchService $searchService, TranslationHelper $translationHelper, array $categoryContentTypes)
    {
        $this->searchService = $searchService;
        $this->translationHelper = $translationHelper;
        $this->categoryContentTypes = $categoryContentTypes;
    }

    /**
     * Loads the item by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function load($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->searchService->findLocations($query);

        if (!empty($result->searchHits)) {
            return $this->buildItems($result)[0];
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
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadByValue($id)
    {
        return $this->load($id);
    }

    /**
     * Returns the category children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubCategories(ItemInterface $item)
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->getId()),
            new Criterion\ContentTypeIdentifier($this->categoryContentTypes),
        );

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 9999;

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
    }

    /**
     * Returns the category children count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return int
     */
    public function getSubCategoriesCount(ItemInterface $item)
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->getId()),
            new Criterion\ContentTypeIdentifier($this->categoryContentTypes),
        );

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Returns the item children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubItems(ItemInterface $item, $offset = 0, $limit = 25)
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->getId()),
        );

        $query = new LocationQuery();
        $query->offset = $offset;
        $query->limit = $limit;
        $query->filter = new Criterion\LogicalAnd($criteria);

        $result = $this->searchService->findLocations($query);

        return $this->buildItems($result);
    }

    /**
     * Returns the item children count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return int
     */
    public function getSubItemsCount(ItemInterface $item)
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->getId()),
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
     * Builds the items from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function buildItems(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return new Item(
                    new Value(
                        $searchHit->valueObject,
                        $this->translationHelper->getTranslatedContentNameByContentInfo(
                            $searchHit->valueObject->contentInfo
                        )
                    )
                );
            },
            $searchResult->searchHits
        );
    }
}
