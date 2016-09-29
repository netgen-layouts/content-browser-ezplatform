<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\ContentBrowserBundle\Config\Configuration;
use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigProcessor;
use PHPUnit\Framework\TestCase;

class EzTagsConfigProcessorTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigProcessor
     */
    protected $configProcessor;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $this->configProcessor = new EzTagsConfigProcessor($this->contentTypeServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigProcessor::getFieldTypeIdentifier
     */
    public function testGetFieldTypeIdentifier()
    {
        $this->assertEquals('eztags', $this->configProcessor->getFieldTypeIdentifier());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigProcessor::getItemType
     */
    public function testGetItemType()
    {
        $this->assertEquals('eztags', $this->configProcessor->getItemType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\EzTagsConfigProcessor::processConfig
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

        $config = new Configuration('eztags', array());
        $this->configProcessor->processConfig(
            'ez-field-definition-eztags-news-tags',
            $config
        );

        $this->assertEquals(array(42), $config->getSections());
        $this->assertEquals(5, $config->getMaxSelected());
    }
}
