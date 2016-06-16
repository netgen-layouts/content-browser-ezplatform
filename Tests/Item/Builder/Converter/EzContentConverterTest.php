<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Builder\Converter;

use eZ\Publish\Core\Repository\Repository;
use Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzContentConverter;
use PHPUnit\Framework\TestCase;

class EzContentConverterTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzContentConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->converter = new EzContentConverter(
            $this->repositoryMock,
            array()
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzContentConverter::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Builder\Converter\EzContentConverter::getValueType
     */
    public function testGetValueType()
    {
        self::assertEquals(
            'ezcontent',
            $this->converter->getValueType()
        );
    }
}
