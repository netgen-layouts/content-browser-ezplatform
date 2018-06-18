<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\EzTags;

use Netgen\ContentBrowser\Item\EzTags\Location;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\ContentBrowser\Item\EzTags\Location
     */
    private $location;

    public function setUp(): void
    {
        $this->tag = new Tag(['id' => 42, 'parentTagId' => 24]);

        $this->location = new Location($this->tag, 'Keyword');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Location::__construct
     * @covers \Netgen\ContentBrowser\Item\EzTags\Location::getLocationId
     */
    public function testGetLocationId(): void
    {
        $this->assertSame(42, $this->location->getLocationId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Location::getName
     */
    public function testGetName(): void
    {
        $this->assertSame('Keyword', $this->location->getName());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Location::getParentId
     */
    public function testGetParentId(): void
    {
        $this->assertSame(24, $this->location->getParentId());
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\EzTags\Location::getTag
     */
    public function testGetTag(): void
    {
        $this->assertSame($this->tag, $this->location->getTag());
    }
}
