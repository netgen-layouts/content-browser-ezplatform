<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzTags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Item\ValueInterface;

class Value implements ValueInterface, EzTagsInterface
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
     * Returns the value name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
