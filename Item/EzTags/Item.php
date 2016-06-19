<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;

class Item implements ItemInterface
{
    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Value
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Value $value
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
     * Returns the value type.
     *
     * @return int|string
     */
    public function getValueType()
    {
        return 'eztags';
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
        return $this->value->getValueObject()->parentTagId;
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
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getValueObject()
    {
        return $this->value->getValueObject();
    }
}
