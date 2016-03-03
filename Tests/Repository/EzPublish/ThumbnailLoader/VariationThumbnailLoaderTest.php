<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Repository\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\Variation;
use eZ\Publish\SPI\Variation\VariationHandler;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader;

class VariationThumbnailLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \eZ\Publish\Core\Helper\FieldHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldHelperMock;

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $variationHandlerMock;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader
     */
    protected $thumbnailLoader;

    public function setUp()
    {
        $this->translationHelperMock = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedField')
            ->will($this->returnValue(new Field()));

        $this->fieldHelperMock = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelperMock
            ->expects($this->any())
            ->method('isFieldEmpty')
            ->will($this->returnValue(false));

        $this->variationHandlerMock = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationHandlerMock
            ->expects($this->any())
            ->method('getVariation')
            ->will($this->returnValue(new Variation(array('uri' => '/image/uri'))));

        $this->thumbnailLoader = new VariationThumbnailLoader(
            $this->translationHelperMock,
            $this->fieldHelperMock,
            $this->variationHandlerMock,
            array('image')
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::loadThumbnail
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::getImageVariation
     */
    public function testLoadThumbnail()
    {
        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(),
            )
        );

        self::assertEquals(
            '/image/uri',
            $this->thumbnailLoader->loadThumbnail($content)
        );
    }
}
