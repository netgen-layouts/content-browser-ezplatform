<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzLocation;

use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class Item implements ItemInterface, CategoryInterface, EzLocationInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $name
     */
    public function __construct(Location $location, $name)
    {
        $this->location = $location;
        $this->name = $name;
    }

    /**
     * Returns the category ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->location->id;
    }

    /**
     * Returns the type.
     *
     * @return int|string
     */
    public function getType()
    {
        return 'ezlocation';
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->location->id;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the parent ID.
     *
     * @return int|string
     */
    public function getParentId()
    {
        return $this->location->parentLocationId != 1 ?
            $this->location->parentLocationId :
            null;
    }

    /**
     * Returns the location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getLocation()
    {
        return $this->location;
    }
}
