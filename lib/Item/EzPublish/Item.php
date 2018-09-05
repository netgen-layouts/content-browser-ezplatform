<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Item implements ItemInterface, LocationInterface, EzPublishInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $content;

    /**
     * @var int|string
     */
    private $value;

    /**
     * @var bool
     */
    private $selectable;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param int|string $value
     * @param bool $selectable
     */
    public function __construct(Location $location, Content $content, $value, bool $selectable = true)
    {
        $this->location = $location;
        $this->content = $content;
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
