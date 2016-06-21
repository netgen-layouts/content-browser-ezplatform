<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzTags;

use Netgen\Bundle\ContentBrowserBundle\Item\CategoryInterface;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class Item implements ItemInterface, CategoryInterface, EzTagsInterface
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
     * Returns the category ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->tag->id;
    }

    /**
     * Returns the type.
     *
     * @return int|string
     */
    public function getType()
    {
        return 'eztags';
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->tag->id;
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
        return $this->tag->parentTagId;
    }

    /**
     * Returns the tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
}
