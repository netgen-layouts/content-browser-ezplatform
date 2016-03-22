<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigLoader;

class EzTagsConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigLoader
     */
    protected $configLoader;

    public function setUp()
    {
        $this->contentTypeServiceMock = self::getMock(ContentTypeService::class);

        $this->configLoader = new EzTagsConfigLoader($this->contentTypeServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigLoader::getItemType
     */
    public function testGetItemType()
    {
        self::assertEquals('eztags', $this->configLoader->getItemType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigLoader::loadConfig
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigLoader::getFieldTypeIdentifier
     */
    public function testLoadConfig()
    {
        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'identifier' => 'tags',
                            'fieldTypeIdentifier' => 'eztags',
                            'fieldSettings' => array(
                                'subTreeLimit' => 42,
                                'maxTags' => 5,
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

        $config = $this->configLoader->loadConfig('ez-field-definition-eztags-news-tags');

        self::assertEquals(
            array(
                'root_items' => array(42),
                'max_selected' => 5,
            ),
            $config
        );
    }
}
