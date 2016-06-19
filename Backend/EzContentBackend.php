<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Value;

class EzContentBackend extends EzLocationBackend
{
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
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId($id),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $result = $this->searchService->findLocations($query);

        if (!empty($result->searchHits)) {
            return $this->buildValues($result)[0];
        }

        throw new NotFoundException(
            sprintf(
                'Item with "%s" ID not found.',
                $id
            )
        );
    }

    /**
     * Builds the items from search result and its hits.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function buildValues(SearchResult $searchResult)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return new Item(
                    new Value(
                        $searchHit->valueObject->contentInfo,
                        $this->translationHelper->getTranslatedContentNameByContentInfo(
                            $searchHit->valueObject->contentInfo
                        )
                    ),
                    $searchHit->valueObject
                );
            },
            $searchResult->searchHits
        );
    }
}
