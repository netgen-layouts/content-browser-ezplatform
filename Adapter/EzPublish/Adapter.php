<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\ContentBrowserBundle\Item\Item;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Adapter\AdapterInterface;

class Adapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder
     */
    protected $itemBuilder;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ItemBuilder $itemBuilder
     */
    public function __construct(
        SearchService $searchService,
        ItemBuilder $itemBuilder
    ) {
        $this->searchService = $searchService;
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * Returns all available columns and their names
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'location_id' => 'netgen_content_browser.ezpublish.columns.location_id',
            'content_id' => 'netgen_content_browser.ezpublish.columns.content_id',
            'thumbnail' => 'netgen_content_browser.ezpublish.columns.thumbnail',
            'type' => 'netgen_content_browser.ezpublish.columns.type',
            'visible' => 'netgen_content_browser.ezpublish.columns.visible',
            'owner' => 'netgen_content_browser.ezpublish.columns.owner',
            'modified' => 'netgen_content_browser.ezpublish.columns.modified',
            'published' => 'netgen_content_browser.ezpublish.columns.published',
            'priority' => 'netgen_content_browser.ezpublish.columns.priority',
            'section' => 'netgen_content_browser.ezpublish.columns.section',
        );
    }

    /**
     * Loads the item for provided ID.
     *
     * @param int|string $itemId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item with provided ID was not found
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\Item
     */
    public function loadItem($itemId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($itemId);
        $result = $this->searchService->findLocations($query);

        if ($result->totalCount == 0) {
            throw new NotFoundException("Item #{$itemId} not found.");
        }

        return $this->itemBuilder->buildItem(
            $result->searchHits[0]->valueObject
        );
    }

    /**
     * Loads all children of the provided item.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\Item $item
     * @param string[] $types
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\Item[]
     */
    public function loadItemChildren(Item $item, array $types = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->id),
        );

        if (!empty($types)) {
            $criteria[] = new Criterion\ContentTypeIdentifier($types);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $result = $this->searchService->findLocations($query);

        $items = array_map(
            function (SearchHit $searchHit) {
                return $this->itemBuilder->buildItem(
                    $searchHit->valueObject
                );
            },
            $result->searchHits
        );

        return $items;
    }

    /**
     * Returns true if provided item has children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\Item $item
     * @param string[] $types
     *
     * @return bool
     */
    public function hasChildren(Item $item, array $types = array())
    {
        $criteria = array(
            new Criterion\ParentLocationId($item->id),
        );

        if (!empty($types)) {
            $criteria[] = new Criterion\ContentTypeIdentifier($types);
        }

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount > 0;
    }

    /**
     * Returns items found with search text
     *
     * @param string $searchText
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\Item[]
     */
    public function search($searchText)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\FullText($searchText);
        $result = $this->searchService->findLocations($query);

        $items = array_map(
            function (SearchHit $searchHit) {
                return $this->itemBuilder->buildItem(
                    $searchHit->valueObject
                );
            },
            $result->searchHits
        );

        return $items;
    }
}
