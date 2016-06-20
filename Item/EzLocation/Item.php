<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzLocation;

use Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class Item implements ItemInterface, CategoryInterface
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Value
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Value $value
     */
    public function __construct(Value $value)
    {
        $this->value = $value;
    }

    /**
     * Returns the item ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->value->getId();
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
        $location = $this->value->getLocation();

        return $location->parentLocationId != 1 ?
            $location->parentLocationId :
            null;
    }

    /**
     * Returns the value.
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\EzLocation\Value
     */
    public function getValue()
    {
        return $this->value;
    }
}
