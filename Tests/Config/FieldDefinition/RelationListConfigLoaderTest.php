<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader;

class RelationListConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader
     */
    protected $configLoader;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $this->configLoader = new RelationListConfigLoader($this->contentTypeServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader::getFieldTypeIdentifier
     */
    public function testGetFieldTypeIdentifier()
    {
        self::assertEquals('ezobjectrelationlist', $this->configLoader->getFieldTypeIdentifier());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader::getItemType
     */
    public function testGetItemType()
    {
        self::assertEquals('ezcontent', $this->configLoader->getItemType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader::loadConfig
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigLoader::getFieldTypeIdentifier
     */
    public function testLoadConfig()
    {
        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'identifier' => 'relation',
                            'fieldTypeIdentifier' => 'ezobjectrelationlist',
                            'fieldSettings' => array(
                                'selectionContentTypes' => array('type1', 'type2'),
                            ),
                        )
                    ),
                ),
            )
        );

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with('news')
            ->will($this->returnValue($contentType));

        $config = $this->configLoader->loadConfig('ez-field-definition-ezobjectrelationlist-news-relation');

        self::assertEquals(
            array(
                'types' => array('type1', 'type2'),
            ),
            $config
        );
    }
}
