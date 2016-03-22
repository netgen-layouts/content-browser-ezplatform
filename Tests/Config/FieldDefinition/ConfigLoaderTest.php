<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition\Stubs\ConfigLoader;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition\Stubs\ConfigLoader
     */
    protected $configLoader;

    public function setUp()
    {
        $this->contentTypeServiceMock = self::getMock(ContentTypeService::class);

        $this->configLoader = new ConfigLoader($this->contentTypeServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::supports
     */
    public function testSupports()
    {
        self::assertTrue(
            $this->configLoader->supports(
                'ez-field-definition-field_type-ng_news-relation'
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::supports
     */
    public function testSupportsReturnsFalse()
    {
        self::assertFalse(
            $this->configLoader->supports(
                'some-config'
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::getFieldDefinition
     */
    public function testLoadConfig()
    {
        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'identifier' => 'relation',
                            'fieldTypeIdentifier' => 'field_type',
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

        $config = $this->configLoader->loadConfig('ez-field-definition-field_type-news-relation');

        self::assertEquals(array('test' => 'config'), $config);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::getFieldDefinition
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Config name format for field definition is not valid.
     */
    public function testLoadConfigWithInvalidConfigName()
    {
        $this->configLoader->loadConfig('ez-field-definition-invalid');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::getFieldDefinition
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage 'news' content type does not exist.
     */
    public function testLoadConfigWithNonExistingContentType()
    {
        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with('news')
            ->will($this->throwException(new NotFoundException('content type', 'news')));

        $this->configLoader->loadConfig('ez-field-definition-field_type-news-relation');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::getFieldDefinition
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage 'relation' field definition does not exist.
     */
    public function testLoadConfigWithNonExistingFieldDefinition()
    {
        $contentType = new ContentType(array('fieldDefinitions' => array()));

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with('news')
            ->will($this->returnValue($contentType));

        $this->configLoader->loadConfig('ez-field-definition-field_type-news-relation');
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader::getFieldDefinition
     * @expectedException \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage 'relation' field definition is not of 'field_type' field type.
     */
    public function testLoadConfigWithInvalidFieldTypeIdentifier()
    {
        $contentType = new ContentType(
            array(
                'fieldDefinitions' => array(
                    new FieldDefinition(
                        array(
                            'identifier' => 'relation',
                            'fieldTypeIdentifier' => 'some_other_field_type',
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

        $this->configLoader->loadConfig('ez-field-definition-field_type-news-relation');
    }
}
