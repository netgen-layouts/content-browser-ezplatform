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
use InvalidArgumentException;

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

        $this->fieldHelperMock = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->variationHandlerMock = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     */
    public function testLoadThumbnail()
    {
        $this->translationHelperMock
            ->expects($this->at(0))
            ->method('getTranslatedField')
            ->will($this->returnValue(new Field()));

        $this->fieldHelperMock
            ->expects($this->at(0))
            ->method('isFieldEmpty')
            ->will($this->returnValue(false));

        $this->variationHandlerMock
            ->expects($this->at(0))
            ->method('getVariation')
            ->will($this->returnValue(new Variation(array('uri' => '/image/uri'))));

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

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::loadThumbnail
     */
    public function testLoadThumbnailWithNoField()
    {
        $this->translationHelperMock
            ->expects($this->at(0))
            ->method('getTranslatedField')
            ->will($this->returnValue(null));

        $this->fieldHelperMock
            ->expects($this->never())
            ->method('isFieldEmpty');

        $this->variationHandlerMock
            ->expects($this->never())
            ->method('getVariation');

        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(),
            )
        );

        self::assertEquals(
            null,
            $this->thumbnailLoader->loadThumbnail($content)
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::loadThumbnail
     */
    public function testLoadThumbnailWithEmptyField()
    {
        $this->translationHelperMock
            ->expects($this->at(0))
            ->method('getTranslatedField')
            ->will($this->returnValue(new Field()));

        $this->fieldHelperMock
            ->expects($this->at(0))
            ->method('isFieldEmpty')
            ->will($this->returnValue(true));

        $this->variationHandlerMock
            ->expects($this->never())
            ->method('getVariation');

        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(),
            )
        );

        self::assertEquals(
            null,
            $this->thumbnailLoader->loadThumbnail($content)
        );
    }

    /**
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::__construct
     * @covers \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\ThumbnailLoader\VariationThumbnailLoader::loadThumbnail
     */
    public function testLoadThumbnailWithNoVariation()
    {
        $this->translationHelperMock
            ->expects($this->at(0))
            ->method('getTranslatedField')
            ->will($this->returnValue(new Field()));

        $this->fieldHelperMock
            ->expects($this->at(0))
            ->method('isFieldEmpty')
            ->will($this->returnValue(false));

        $this->variationHandlerMock
            ->expects($this->at(0))
            ->method('getVariation')
            ->will($this->throwException(new InvalidArgumentException()));

        $content = new Content(
            array(
                'versionInfo' => new VersionInfo(),
            )
        );

        self::assertEquals(
            null,
            $this->thumbnailLoader->loadThumbnail($content)
        );
    }
}
