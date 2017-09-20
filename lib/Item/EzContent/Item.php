<?php

namespace Netgen\ContentBrowser\Item\EzContent;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

class Item implements ItemInterface, LocationInterface, EzContentInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $content;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $selectable;

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

    public function getLocationId()
    {
        return $this->location->id;
    }

    public function getValue()
    {
        return $this->content->contentInfo->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentId()
    {
        $parentId = (int) $this->location->parentLocationId;

        return $parentId !== 1 ? $parentId : null;
    }

    public function isVisible()
    {
        return !$this->location->invisible;
    }

    public function isSelectable()
    {
        return $this->selectable;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getContent()
    {
        return $this->content;
    }
}
