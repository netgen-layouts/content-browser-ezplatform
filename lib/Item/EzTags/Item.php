<?php

namespace Netgen\ContentBrowser\Item\EzTags;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class Item implements ItemInterface, LocationInterface, EzTagsInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var string
     */
    private $name;

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

    public function getLocationId()
    {
        return $this->tag->id;
    }

    public function getValue()
    {
        return $this->tag->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentId()
    {
        return $this->tag->parentTagId;
    }

    public function isVisible()
    {
        return true;
    }

    public function isSelectable()
    {
        return true;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
