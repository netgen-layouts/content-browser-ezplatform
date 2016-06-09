<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Backend;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend;
use PHPUnit\Framework\TestCase;

class EzTagsBackendTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsServiceMock;

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

        $this->config = array(
            'root_items' => array(4, 2),
            'default_limit' => 25,
        );

        $this->languages = array('eng-GB', 'cro-HR');

        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->config,
            $this->languages
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getSections
     */
    public function testGetSections()
    {
        foreach ($this->config['root_items'] as $index => $rootItemId) {
            $this->tagsServiceMock
                ->expects($this->at($index))
                ->method('loadTag')
                ->with($this->equalTo($rootItemId))
                ->will($this->returnValue(new Tag()));
        }

        $sections = $this->backend->getSections();

        self::assertCount(2, $sections);
        foreach ($sections as $section) {
            self::assertInstanceOf(Tag::class, $section);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::loadItem
     */
    public function testLoadItem()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new Tag()));

        $tag = $this->backend->loadItem(1);

        self::assertInstanceOf(Tag::class, $tag);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::loadItem
     */
    public function testLoadItemWithEmptyItemId()
    {
        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTag');

        $tag = $this->backend->loadItem(0);

        self::assertEquals(
            new Tag(
                array(
                    'id' => 0,
                    'keywords' => array(
                        'eng-GB' => 'All tags',
                    ),
                    'mainLanguageCode' => 'eng-GB',
                    'alwaysAvailable' => true,
                )
            ),
            $tag
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::loadItem
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException
     */
    public function testLoadItemThrowsNotFoundException()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->throwException(new NotFoundException('tag', 1)));

        $this->backend->loadItem(1);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::loadItems
     */
    public function testLoadItems()
    {
        $this->tagsServiceMock
            ->expects($this->at(0))
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new Tag(array('id' => 1))));

        $this->tagsServiceMock
            ->expects($this->at(1))
            ->method('loadTag')
            ->with($this->equalTo(2))
            ->will($this->returnValue(new Tag(array('id' => 2))));

        $items = $this->backend->loadItems(array(1, 2));

        self::assertCount(2, $items);

        self::assertEquals(new Tag(array('id' => 1)), $items[0]);
        self::assertEquals(new Tag(array('id' => 2)), $items[1]);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     */
    public function testGetChildren()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new Tag()));

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo(new Tag()),
                $this->equalTo(0),
                $this->equalTo(-1)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $items = $this->backend->getChildren(1);

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(Tag::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     */
    public function testGetChildrenWithEmptyItemId()
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

        $items = $this->backend->getChildren(0);

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(Tag::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildren
     */
    public function testGetChildrenWithParams()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new Tag()));

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTagChildren')
            ->with(
                $this->equalTo(new Tag()),
                $this->equalTo(5),
                $this->equalTo(10)
            )
            ->will($this->returnValue(array(new Tag(), new Tag())));

        $items = $this->backend->getChildren(
            1,
            array(
                'offset' => 5,
                'limit' => 10,
            )
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(Tag::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildrenCount
     */
    public function testGetChildrenCount()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('loadTag')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new Tag()));

        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo(new Tag()))
            ->will($this->returnValue(2));

        $count = $this->backend->getChildrenCount(1);

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::getChildrenCount
     */
    public function testGetChildrenCountWithEmptyItemId()
    {
        $this->tagsServiceMock
            ->expects($this->once())
            ->method('getTagChildrenCount')
            ->with($this->equalTo(null))
            ->will($this->returnValue(2));

        $count = $this->backend->getChildrenCount(0);

        self::assertEquals(2, $count);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
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

        $items = $this->backend->search('test');

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(Tag::class, $item);
        }
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
     */
    public function testSearchWithNoLanguages()
    {
        $this->backend = new EzTagsBackend(
            $this->tagsServiceMock,
            $this->config,
            array()
        );

        $this->tagsServiceMock
            ->expects($this->never())
            ->method('loadTagsByKeyword');

        $items = $this->backend->search('test');

        self::assertCount(0, $items);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Backend\EzTagsBackend::search
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

        $items = $this->backend->search(
            'test',
            array('offset' => 5, 'limit' => 10)
        );

        self::assertCount(2, $items);
        foreach ($items as $item) {
            self::assertInstanceOf(Tag::class, $item);
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
