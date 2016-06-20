<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzTags;

interface EzTagsInterface
{
    /**
     * Returns the tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
