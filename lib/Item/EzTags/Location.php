<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzTags;

use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class Location implements LocationInterface, EzTagsInterface
{
    private Tag $tag;

    private string $name;

    public function __construct(Tag $tag, string $name)
    {
        $this->tag = $tag;
        $this->name = $name;
    }

    public function getLocationId(): int
    {
        return (int) $this->tag->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): ?int
    {
        return $this->tag->parentTagId !== null ? (int) $this->tag->parentTagId : null;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
