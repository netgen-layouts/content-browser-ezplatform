<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Tests\Item\EzTags;

use Netgen\ContentBrowser\Ez\Item\EzTags\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    private Tag $tag;

    private Location $location;

    protected function setUp(): void
    {
        $this->tag = new Tag(['id' => 42, 'parentTagId' => 24]);

        $this->location = new Location($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Location::__construct
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Location::getLocationId
     */
    public function testGetLocationId(): void
    {
        self::assertSame(42, $this->location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Location::getName
     */
    public function testGetName(): void
    {
        self::assertSame('Keyword', $this->location->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Location::getParentId
     */
    public function testGetParentId(): void
    {
        self::assertSame(24, $this->location->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Ez\Item\EzTags\Location::getTag
     */
    public function testGetTag(): void
    {
        self::assertSame($this->tag, $this->location->getTag());
    }
}
