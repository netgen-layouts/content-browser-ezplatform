<?php

namespace Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader\ThumbnailLoaderInterface;
use DateTime;

class ItemBuilder
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
     * @var \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader\ThumbnailLoaderInterface
     */
    protected $thumbnailLoader;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\ThumbnailLoader\ThumbnailLoaderInterface $thumbnailLoader
     */
    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper,
        ThumbnailLoaderInterface $thumbnailLoader
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
        $this->thumbnailLoader = $thumbnailLoader;
    }

    /**
     * Builds the browser item from API location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Adapter\EzPublish\Item
     */
    public function buildItem(Location $location)
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

        return new Item(
            $location,
            array(
                'id' => $location->id,
                'parentId' => $location->parentLocationId,
                'path' => array_map(function ($v) { return (int)$v; }, $path),
                'name' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $location->contentInfo
                ),
                'isEnabled' => true,
                'additionalColumns' => array(
                    'thumbnail' => $this->thumbnailLoader->loadThumbnail($content),
                    'type' => $this->translationHelper->getTranslatedByMethod(
                        $this->repository->getContentTypeService()->loadContentType(
                            $location->contentInfo->contentTypeId
                        ),
                        'getName'
                    ),
                    'visible' => !$location->invisible,
                    'owner' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                        $ownerContentInfo
                    ),
                    'modified' => $location->contentInfo->modificationDate->format(Datetime::ISO8601),
                    'published' => $location->contentInfo->publishedDate->format(Datetime::ISO8601),
                    'priority' => $location->priority,
                    'section' => $this->repository->getSectionService()->loadSection(
                        $location->contentInfo->sectionId
                    )->name,
                )
            )
        );
    }
}
