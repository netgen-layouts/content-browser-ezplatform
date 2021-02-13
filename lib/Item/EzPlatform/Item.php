<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzPlatform;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Item implements ItemInterface, LocationInterface, EzPlatformInterface
{
    private Location $location;

    private Content $content;

    /**
     * @var int|string
     */
    private $value;

    private bool $selectable;

    /**
     * @param int|string $value
     */
    public function __construct(Location $location, $value, bool $selectable = true)
    {
        $this->location = $location;
        $this->content = $location->getContent();
        $this->value = $value;
        $this->selectable = $selectable;
    }

    public function getLocationId()
    {
        return $this->location->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->content->getName() ?? '';
    }

    public function getParentId()
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
