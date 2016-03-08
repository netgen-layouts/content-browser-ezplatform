<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Values\Content\Content;
use Exception;

class VariationThumbnailLoader implements ThumbnailLoaderInterface
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * @var \eZ\Publish\Core\Helper\FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    protected $variationHandler;

    /**
     * @var array
     */
    protected $imageFields;

    /**
     * @var string
     */
    protected $variationName;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\Core\Helper\FieldHelper $fieldHelper
     * @param \eZ\Publish\SPI\Variation\VariationHandler $variationHandler
     * @param array $imageFields
     * @param string $variationName
     */
    public function __construct(
        TranslationHelper $translationHelper,
        FieldHelper $fieldHelper,
        VariationHandler $variationHandler,
        array $imageFields,
        $variationName
    ) {
        $this->translationHelper = $translationHelper;
        $this->fieldHelper = $fieldHelper;
        $this->variationHandler = $variationHandler;
        $this->imageFields = $imageFields;
        $this->variationName = $variationName;
    }

    /**
     * Loads the thumbnail image for provided eZ Publish content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return string
     */
    public function loadThumbnail(Content $content)
    {
        foreach ($this->imageFields as $imageField) {
            $field = $this->translationHelper->getTranslatedField($content, $imageField);
            if (!$field instanceof Field || $this->fieldHelper->isFieldEmpty($content, $imageField)) {
                continue;
            }

            try {
                $imageVariation = $this->variationHandler->getVariation(
                    $field,
                    $content->versionInfo,
                    $this->variationName
                );
            } catch (Exception $e) {
                continue;
            }

            return $imageVariation->uri;
        }

        return null;
    }
}
