<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzTags;

use DateTimeImmutable;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified;
use Netgen\ContentBrowser\Item\EzTags\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

final class ModifiedTest extends TestCase
{
    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new Modified('d.m.Y H:i:s');
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified::getValue
     */
    public function testGetValue(): void
    {
        $date = new DateTimeImmutable();
        $date = $date->setDate(2016, 7, 17);
        $date = $date->setTime(18, 15, 42);

        $item = new Item(
            new Tag(
                [
                    'modificationDate' => $date,
                ]
            ),
            'Name'
        );

        $this->assertSame(
            '17.07.2016 18:15:42',
            $this->provider->getValue($item)
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzTags\Modified::getValue
     */
    public function testGetValueWithInvalidItem(): void
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
