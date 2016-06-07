<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class EzContentBackend extends EzLocationBackend
{
    /**
     * Loads items for provided value IDs.
     *
     * @param array $valueIds
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function loadItems(array $valueIds = array())
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId($valueIds),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN)
            )
        );

        $result = $this->searchService->findLocations($query);

        return $this->extractValueObjects($result);
    }
}
