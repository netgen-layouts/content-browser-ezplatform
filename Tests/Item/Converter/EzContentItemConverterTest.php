<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Item\Converter;

use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzContentItemConverter;

class EzContentItemConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzContentItemConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock(Repository::class);

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->converter = new EzContentItemConverter(
            $this->repositoryMock,
            $this->translationHelperMock,
            array()
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Item\Converter\EzContentItemConverter::getValue
     */
    public function testGetValue()
    {
        self::assertEquals(
            84,
            $this->converter->getValue($this->getValueObject())
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected function getValueObject()
    {
        return new Location(
            array(
                'id' => 42,
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => 84,
                    )
                ),
            )
        );
    }
}
