<?php

namespace Netgen\Bundle\ContentBrowserBundle\Value\Loader;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Value\EzLocation;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface;

class EzLocationValueLoader implements ValueLoaderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    public $searchService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    public $translationHelper;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(SearchService $searchService, TranslationHelper $translationHelper)
    {
        $this->searchService = $searchService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Loads the value by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function load($id)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LocationId($id);

        $result = $this->searchService->findLocations($query);

        if (!empty($result->searchHits)) {
            return $this->buildValue($result->searchHits[0]->valueObject);
        }

        throw new NotFoundException(
            sprintf(
                'Value with "%s" ID not found.',
                $id
            )
        );
    }

    /**
     * Loads the value by its internal value.
     *
     * @param int|string $value
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function loadByValue($value)
    {
        return $this->load($value);
    }

    /**
     * Builds the value from provided value object.
     *
     * @param mixed $valueObject
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function buildValue($valueObject)
    {
        return new EzLocation(
            $valueObject,
            $this->translationHelper->getTranslatedContentNameByContentInfo(
                $valueObject->contentInfo
            )
        );
    }
}
