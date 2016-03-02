<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

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
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
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

        return new Location(
            $location,
            array(
                'id' => $location->id,
                'parentId' => $location->parentLocationId,
                'name' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $location->contentInfo
                ),
                'isEnabled' => true,
                'thumbnail' => null,
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
}
