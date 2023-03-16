<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\Ibexa;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Item implements ItemInterface, LocationInterface, IbexaInterface
{
    private Content $content;

    public function __construct(private Location $location, private int $value, private bool $selectable = true)
    {
        $this->content = $this->location->getContent();
    }

    public function getLocationId(): int
    {
        return (int) $this->location->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->content->getName() ?? '';
    }

    public function getParentId(): ?int
    {
        $parentId = (int) $this->location->parentLocationId;

        return $parentId !== 1 ? $parentId : null;
    }

    public function isVisible(): bool
    {
        return !$this->location->invisible;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
