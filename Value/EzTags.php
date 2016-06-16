<?php

namespace Netgen\Bundle\ContentBrowserBundle\Value;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class EzTags implements ValueInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param string $name
     */
    public function __construct(Tag $tag, $name)
    {
        $this->tag = $tag;
        $this->name = $name;
    }

    /**
     * Returns the value ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->tag->id;
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
     * Returns the item value.
     *
     * @return int|string
     */
    public function getValue()
    {
        return $this->tag->id;
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the item parent ID.
     *
     * @return int|string
     */
    public function getParentId()
    {
        return $this->tag->parentTagId;
    }

    /**
     * Returns the value object.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getValueObject()
    {
        return $this->tag;
    }
}
