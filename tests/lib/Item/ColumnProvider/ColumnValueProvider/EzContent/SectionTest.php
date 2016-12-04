<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzContent;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section as EzSection;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Section;
use Netgen\ContentBrowser\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionServiceMock;

    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Section
     */
    protected $provider;

    public function setUp()
    {
        $this->sectionServiceMock = $this->createMock(SectionService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('sudo', 'getSectionService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(function ($callback) {
                return $callback($this->repositoryMock);
            }));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getSectionService')
            ->will($this->returnValue($this->sectionServiceMock));

        $this->provider = new Section(
            $this->repositoryMock
        );
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Section::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzContent\Section::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(),
            new ContentInfo(
                array(
                    'sectionId' => 42,
                )
            ),
            'Name'
        );

        $section = new EzSection(
            array(
                'name' => 'Section name',
            )
        );

        $this->sectionServiceMock
            ->expects($this->once())
            ->method('loadSection')
            ->with($this->equalTo(42))
            ->will($this->returnValue($section));

        $this->assertEquals(
            'Section name',
            $this->provider->getValue($item)
        );
    }
}
