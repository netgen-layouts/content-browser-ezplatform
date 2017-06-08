<?php

namespace Netgen\ContentBrowser\Item\EzLocation;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

class Item implements ItemInterface, LocationInterface, EzLocationInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $selectable;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $name
     * @param bool $selectable
     */
    public function __construct(Location $location, Content $content, $name, $selectable = true)
    {
        $this->location = $location;
        $this->content = $content;
        $this->name = $name;
        $this->selectable = $selectable;
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
     * Returns if the item is selectable.
     *
     * @return bool
     */
    public function isSelectable()
    {
        return $this->selectable;
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

    /**
     * Returns the content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function getContent()
    {
        return $this->content;
    }
}
