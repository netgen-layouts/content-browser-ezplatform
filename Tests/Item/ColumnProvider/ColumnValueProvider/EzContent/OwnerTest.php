<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\ColumnProvider\ColumnValueProvider\EzContent;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\Owner;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class OwnerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\Owner
     */
    protected $provider;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('getContentService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($this->contentServiceMock));

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->provider = new Owner(
            $this->repositoryMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\Owner::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\Owner::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(),
            new ContentInfo(
                array(
                    'ownerId' => 42,
                )
            ),
            'Name'
        );

        $ownerContentInfo = new ContentInfo();

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo(42))
            ->will($this->returnValue($ownerContentInfo));

        $this->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedContentNameByContentInfo')
            ->with($this->equalTo($ownerContentInfo))
            ->will($this->returnValue('Owner name'));

        $this->assertEquals(
            'Owner name',
            $this->provider->getValue($item)
        );
    }
}
