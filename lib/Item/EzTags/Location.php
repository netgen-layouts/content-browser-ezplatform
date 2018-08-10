<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzTags;

use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class Location implements LocationInterface, EzTagsInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var string
     */
    private $name;

    public function __construct(Tag $tag, string $name)
    {
        $this->tag = $tag;
        $this->name = $name;
    }

    public function getLocationId()
    {
        return $this->tag->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId()
    {
        return $this->tag->parentTagId;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
