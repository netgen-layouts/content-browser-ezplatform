<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzTags;

interface ItemInterface
{
    /**
     * Returns the API tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
