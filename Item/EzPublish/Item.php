<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\Item as BaseItem;

class Item extends BaseItem implements ItemInterface
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
    public function __construct(Location $location, array $properties = array())
    {
        parent::__construct($properties);

        $this->location = $location;
    }

    /**
     * Returns the API location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation()
    {
        return $this->location;
    }
}
