<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzTags;

interface LocationInterface
{
    /**
     * Returns the API tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
