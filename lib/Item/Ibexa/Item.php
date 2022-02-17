<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\Ibexa;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Item implements ItemInterface, LocationInterface, IbexaInterface
{
    private Location $location;

    private Content $content;

    private int $value;

    private bool $selectable;

    public function __construct(Location $location, int $value, bool $selectable = true)
    {
        $this->location = $location;
        $this->content = $location->getContent();
        $this->value = $value;
        $this->selectable = $selectable;
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
