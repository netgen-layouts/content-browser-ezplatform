<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Item\EzTags;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

final class Item implements ItemInterface, LocationInterface, EzTagsInterface
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

    public function getValue()
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

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
