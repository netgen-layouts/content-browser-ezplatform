<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\NetgenTags;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class Item implements ItemInterface, LocationInterface, NetgenTagsInterface
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

    public function getValue(): int
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

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return $this->tag->id !== 0;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
