<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Item\EzTags;

interface EzTagsInterface
{
    /**
     * Returns the tag.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTag();
}
