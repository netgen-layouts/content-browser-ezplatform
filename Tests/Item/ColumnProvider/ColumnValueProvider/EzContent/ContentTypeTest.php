<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\ColumnProvider\ColumnValueProvider\EzContent;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType as EzContentType;
use Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentType;
use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentType
     */
    protected $provider;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('getContentTypeService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($this->contentTypeServiceMock));

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->provider = new ContentType(
            $this->repositoryMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentType::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\ColumnProvider\ColumnValueProvider\EzContent\ContentType::getValue
     */
    public function testGetValue()
    {
        $item = new Item(
            new Location(),
            new ContentInfo(
                array(
                    'contentTypeId' => 42,
                )
            ),
            'Name'
        );

        $contentType = new EzContentType(
            array(
                'fieldDefinitions' => array(),
            )
        );

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(42))
            ->will($this->returnValue($contentType));

        $this->translationHelperMock
            ->expects($this->once())
            ->method('getTranslatedByMethod')
            ->with($this->equalTo($contentType), $this->equalTo('getName'))
            ->will($this->returnValue('Content type'));

        $this->assertEquals(
            'Content type',
            $this->provider->getValue($item)
        );
    }
}
