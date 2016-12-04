<?php

namespace Netgen\ContentBrowser\Item\EzLocation;

use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

class Item implements ItemInterface, LocationInterface, EzLocationInterface
{
    const TYPE = 'ezlocation';

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
     * Returns the location ID.
     *
     * @return int|string
     */
    public function getLocationId()
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
        return static::TYPE;
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
        $parentId = (int) $this->location->parentLocationId;

        return $parentId !== 1 ? $parentId : null;
    }

    /**
     * Returns if the item is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return !$this->location->invisible;
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
