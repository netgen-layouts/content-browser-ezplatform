<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\OutOfBoundsException;
use Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Repository\AdapterInterface;

class Adapter implements AdapterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(
        SearchService $searchService,
        ContentTypeService $contentTypeService,
        TranslationHelper $translationHelper
    ) {
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Loads the location for provided ID.
     *
     * @param int|string $locationId
     * @param array $rootLocationIds
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location with provided ID was not found
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\OutOfBoundsException If location is outside of provided root locations
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface
     */
    public function loadLocation($locationId, $rootLocationIds)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($locationId);
        $result = $this->searchService->findLocations($query);

        if ($result->totalCount == 0) {
            throw new NotFoundException("Location #{$locationId} not found.");
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $apiLocation */
        $apiLocation = $result->searchHits[0]->valueObject;
        foreach ($rootLocationIds as $rootLocationId) {
            if (strpos($apiLocation->pathString, '/' . $rootLocationId . '/') !== false) {
                return $this->buildDomainLocation($apiLocation);
            }
        }

        throw new OutOfBoundsException("Location #{$locationId} is not inside root locations.");
    }

    /**
     * Loads all children of the provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface $location
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface[]
     */
    public function loadLocationChildren(LocationInterface $location)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\ParentLocationId($location->getId());
        $result = $this->searchService->findLocations($query);

        $locations = array_map(
            function (SearchHit $searchHit) {
                return $this->buildDomainLocation(
                    $searchHit->valueObject
                );
            },
            $result->searchHits
        );

        return $locations;
    }

    /**
     * Returns true if provided location has children.
     *
     * @param int|string $locationId
     *
     * @return bool
     */
    protected function locationHasChildren($locationId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\ParentLocationId($locationId);
        $query->limit = 0;

        $result = $this->searchService->findLocations($query);

        return $result->totalCount > 0;
    }

    /**
     * Builds the object implementing LocationInterface.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $apiLocation
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface
     */
    protected function buildDomainLocation(APILocation $apiLocation)
    {
        return new Location(
            $apiLocation,
            $this->translationHelper->getTranslatedContentNameByContentInfo(
                $apiLocation->contentInfo
            ),
            $this->translationHelper->getTranslatedByMethod(
                $this->contentTypeService->loadContentType(
                    $apiLocation->contentInfo->contentTypeId
                ),
                'getName'
            ),
            $this->locationHasChildren($apiLocation->id)
        );
    }
}
