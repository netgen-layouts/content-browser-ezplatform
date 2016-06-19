<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use Netgen\Bundle\ContentBrowserBundle\Value\EzTags;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend;
use PHPUnit\Framework\TestCase;

class EzTagsBackendTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueLoaderMock;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $languages = array();

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend
     */
    protected $backend;

    public function setUp()
    {
        $this->tagsServiceMock = $this->createMock(TagsService::class);

        $this->valueLoaderMock = $this->createMock(ValueLoaderInterface::class);

        $this->valueLoaderMock
            ->expects($this->any())
            ->method('buildValue')
            ->will(
                $this->returnCallback(
                    function ($valueObject) {
                        return new EzTags($valueObject, 'name');
                    }
                )
            );

        $this->config = array(
            'sections' => array(4, 2),
            'default_limit' => 25,
        );

        $this->languages = array('eng-GB', 'cro-HR');

        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->valueLoaderMock,
            $this->config,
            $this->languages
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testGetChildren()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo(new Tag(array('id' => 1))),
                $this->equalTo(0),
                $this->equalTo(-1)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $values = $this->backend->getChildren(new EzTags(new Tag(array('id' => 1)), 'tag'));

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testGetChildrenWithEmptyValueId()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo(null),
                $this->equalTo(0),
                $this->equalTo(-1)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $values = $this->backend->getChildren(new EzTags(new Tag(array('id' => 0)), ''));

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testGetChildrenWithParams()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo(new Tag(array('id' => 1))),
                $this->equalTo(5),
                $this->equalTo(10)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $values = $this->backend->getChildren(
            new EzTags(new Tag(array('id' => 1)), 'tag'),
            array(
                'offset' => 5,
                'limit' => 10,
            )
        );

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo(new Tag(array('id' => 1))))
            ->will($this->returnValue(2));

        $count = $this->backend->getChildrenCount(new EzTags(new Tag(array('id' => 1)), 'tag'));

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildrenCount
     */
    public function testGetChildrenCountWithEmptyValueId()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo(null))
            ->will($this->returnValue(2));

        $count = $this->backend->getChildrenCount(new EzTags(new Tag(array('id' => 0)), ''));

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testSearch()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true),
                $this->equalTo(0),
                $this->equalTo($this->config['default_limit'])
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $values = $this->backend->search('test');

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testSearchWithNoLanguages()
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->valueLoaderMock,
            $this->config,
            array()
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagsByKeyword');

        $values = $this->backend->search('test');

        self::assertCount(0, $values);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::buildItems
     */
    public function testSearchWithParams()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagsByKeyword')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true),
                $this->equalTo(5),
                $this->equalTo(10)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $values = $this->backend->search(
            'test',
            array('offset' => 5, 'limit' => 10)
        );

        self::assertCount(2, $values);
        foreach ($values as $value) {
            self::assertInstanceOf(ValueInterface::class, $value);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagsByKeywordCount')
            ->with(
                $this->equalTo('test'),
                $this->equalTo('eng-GB'),
                $this->equalTo(true)
            )
            ->will($this->returnValue(2));

        $count = $this->backend->searchCount('test');

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::searchCount
     */
    public function testSearchCountWithNoLanguages()
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->valueLoaderMock,
            $this->config,
            array()
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('getTagsByKeywordCount');

        $count = $this->backend->searchCount('test');

        self::assertEquals(0, $count);
    }
}
