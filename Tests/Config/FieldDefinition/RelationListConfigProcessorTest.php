<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\ContentBrowserBundle\Config\Configuration;
use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor;
use PHPUnit\Framework\TestCase;

class RelationListConfigProcessorTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeServiceMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor
     */
    protected $configProcessor;

    public function setUp()
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $this->configProcessor = new RelationListConfigProcessor($this->contentTypeServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor::getFieldTypeIdentifier
     */
    public function testGetFieldTypeIdentifier()
    {
        $this->assertEquals('ezobjectrelationlist', $this->configProcessor->getFieldTypeIdentifier());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor::getValueType
     */
    public function testGetValueType()
    {
        $this->assertEquals('ezcontent', $this->configProcessor->getValueType());
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor::processConfig
     * @covers \Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\RelationListConfigProcessor::getFieldTypeIdentifier
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

        $config = new Configuration('ezcontent', array());
        $this->configProcessor->processConfig(
            'ez-field-definition-ezobjectrelationlist-news-relation',
            $config
        );

        $this->assertTrue($config->hasParameter('types'));
        $this->assertEquals(array('type1', 'type2'), $config->getParameter('types'));
    }
}
