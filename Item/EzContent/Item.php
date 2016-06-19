<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzContent;

use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class Item implements ItemInterface
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Value
     */
    protected $value;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * Constructor.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Value $value
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function __construct(Value $value, Location $location)
    {
        $this->value = $value;
        $this->location = $location;
    }

    /**
     * Returns the item ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->location->id;
    }

    /**
     * Returns the value type.
     *
     * @return int|string
     */
    public function getValueType()
    {
        return 'ezcontent';
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->value->getName();
    }

    /**
     * Returns the item parent ID.
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
     * Returns the value.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the value object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getValueObject()
    {
        return $this->location;
    }
}
