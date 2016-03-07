<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Tree\Location as BaseLocation;
use Netgen\Bundle\ContentBrowserBundle\Tree\EzPublish\LocationInterface as EzPublishLocationInterface;

class Location extends BaseLocation implements EzPublishLocationInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $properties
     */
    public function __construct(APILocation $location, array $properties = array())
    {
        parent::__construct($properties);

        $this->location = $location;
    }

    /**
     * Returns the API location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getAPILocation()
    {
        return $this->location;
    }
}
