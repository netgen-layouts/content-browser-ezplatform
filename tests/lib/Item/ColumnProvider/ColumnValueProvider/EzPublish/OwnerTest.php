<?php

namespace Netgen\ContentBrowser\Tests\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner;
use Netgen\ContentBrowser\Item\EzPublish\Item;
use Netgen\ContentBrowser\Tests\Stubs\Item as StubItem;
use PHPUnit\Framework\TestCase;

class OwnerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $translationHelperMock;

    /**
     * @var \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner
     */
    private $provider;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('sudo', 'getContentService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(function ($callback) {
                return $callback($this->repositoryMock);
            }));

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
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner::__construct
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner::getValue
     */
    public function testGetValue()
    {
        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'ownerId' => 42,
                            )
                        ),
                    )
                ),
            )
        );

        $item = new Item(
            new Location(),
            $content,
            null,
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

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner::getValue
     */
    public function testGetValueWithNonExistingOwner()
    {
        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'ownerId' => 42,
                            )
                        ),
                    )
                ),
            )
        );

        $item = new Item(
            new Location(),
            $content,
            null,
            'Name'
        );

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo(42))
            ->will($this->throwException(new NotFoundException('user', 42)));

        $this->translationHelperMock
            ->expects($this->never())
            ->method('getTranslatedContentNameByContentInfo');

        $this->assertEquals('', $this->provider->getValue($item));
    }

    /**
     * @covers \Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish\Owner::getValue
     */
    public function testGetValueWithInvalidItem()
    {
        $this->assertNull($this->provider->getValue(new StubItem()));
    }
}
