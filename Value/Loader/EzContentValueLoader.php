<?php

namespace Netgen\Bundle\ContentBrowserBundle\Value\Loader;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Value\EzContent;

class EzContentValueLoader extends EzLocationValueLoader
{
    /**
     * Returns the value type this loader supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'ezcontent';
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
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentId($value),
                new Criterion\Location\IsMainLocation(Criterion\Location\IsMainLocation::MAIN),
            )
        );

        $result = $this->searchService->findLocations($query);

        if (!empty($result->searchHits)) {
            return $this->buildValue($result->searchHits[0]->valueObject);
        }

        throw new NotFoundException();
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
        return new EzContent(
            $valueObject,
            $this->translationHelper->getTranslatedContentNameByContentInfo(
                $valueObject->contentInfo
            )
        );
    }
}
