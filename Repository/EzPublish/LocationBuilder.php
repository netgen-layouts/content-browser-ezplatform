<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\Variation;
use Exception;

class LocationBuilder
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

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
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \eZ\Publish\Core\Helper\FieldHelper $fieldHelper
     * @param \eZ\Publish\SPI\Variation\VariationHandler $variationHandler
     * @param array $imageFields
     */
    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper,
        FieldHelper $fieldHelper,
        VariationHandler $variationHandler,
        array $imageFields
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
        $this->fieldHelper = $fieldHelper;
        $this->variationHandler = $variationHandler;
        $this->imageFields = $imageFields;
    }

    /**
     * Builds the browser location from API location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\Location
     */
    public function buildLocation(APILocation $location)
    {
        $ownerContentInfo = $this->repository->sudo(
            function (Repository $repository) use ($location) {
                return $repository->getContentService()->loadContentInfo(
                    $location->contentInfo->ownerId
                );
            }
        );

        $content = $this->repository->getContentService()->loadContentByContentInfo(
            $location->contentInfo
        );

        // First element is location ID == 1. We don't need it.
        $path = $location->path;
        array_shift($path);

        return new Location(
            $location,
            array(
                'id' => $location->id,
                'parentId' => $location->parentLocationId,
                'path' => array_map(function($v) { return (int)$v; }, $path),
                'name' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $location->contentInfo
                ),
                'isEnabled' => true,
                'thumbnail' => $this->getThumbnail($content),
                'type' => $this->translationHelper->getTranslatedByMethod(
                    $this->repository->getContentTypeService()->loadContentType(
                        $location->contentInfo->contentTypeId
                    ),
                    'getName'
                ),
                'isVisible' => !$location->invisible,
                'owner' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $ownerContentInfo
                ),
                'modified' => $location->contentInfo->modificationDate,
                'published' => $location->contentInfo->publishedDate,
                'priority' => $location->priority,
                'section' => $this->repository->getSectionService()->loadSection(
                    $location->contentInfo->sectionId
                )->name,
            )
        );
    }

    /**
     * Returns path to the thumbnail for provided content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return string
     */
    protected function getThumbnail(Content $content)
    {
        foreach ($this->imageFields as $imageField) {
            $field = $this->translationHelper->getTranslatedField($content, $imageField);
            if (!$field instanceof Field || $this->fieldHelper->isFieldEmpty($content, $imageField)) {
                continue;
            }

            $imageVariation = $this->getImageVariation($field, $content->versionInfo);
            if (!$imageVariation instanceof Variation) {
                continue;
            }

            return $imageVariation->uri;
        }

        return null;
    }

    /**
     * Returns the image variation object for $field and $versionInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     */
    public function getImageVariation(Field $field, VersionInfo $versionInfo)
    {
        try {
            return $this->variationHandler->getVariation(
                $field,
                $versionInfo,
                'netgen_content_browser'
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
