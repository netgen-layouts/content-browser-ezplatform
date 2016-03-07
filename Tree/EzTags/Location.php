<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzTags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Tree\Location as BaseLocation;
use Netgen\Bundle\ContentBrowserBundle\Tree\EzTags\LocationInterface as EzTagsLocationInterface;

class Location extends BaseLocation implements EzTagsLocationInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param array $properties
     */
    public function __construct(Tag $tag, array $properties = array())
    {
        parent::__construct($properties);

        $this->tag = $tag;
    }

    /**
     * Returns the API tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
}
