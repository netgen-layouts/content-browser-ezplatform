<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzTags;

interface ItemInterface
{
    /**
     * Returns the API tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
