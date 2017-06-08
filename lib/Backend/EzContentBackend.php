<?php

namespace Netgen\ContentBrowser\Backend;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\EzContent\Item;

class EzContentBackend extends EzLocationBackend
{
    /**
     * Loads the item by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\ContentBrowser\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\ContentBrowser\Item\ItemInterface
     */
    public function loadItem($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId($id),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $result = $this->repository->getSearchService()->findLocations(
            $query,
            array('languages' => $this->languages)
        );

        if (!empty($result->searchHits)) {
            return $this->buildItem($result->searchHits[0]);
        }

        throw new NotFoundException(
            sprintf(
                'Item with ID %s not found.',
                $id
            )
        );
    }

    /**
     * Builds the item from provided search hit.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $searchHit
     *
     * @return \Netgen\ContentBrowser\Item\EzContent\Item
     */
    protected function buildItem(SearchHit $searchHit)
    {
        $content = $this->repository->sudo(
            function (Repository $repository) use ($searchHit) {
                return $repository->getContentService()->loadContentByContentInfo(
                    $searchHit->valueObject->contentInfo
                );
            }
        );

        $name = $this->translationHelper->getTranslatedContentNameByContentInfo(
            $searchHit->valueObject->contentInfo
        );

        return new Item(
            $searchHit->valueObject,
            $content,
            $name,
            $this->isSelectable($content)
        );
    }
}
