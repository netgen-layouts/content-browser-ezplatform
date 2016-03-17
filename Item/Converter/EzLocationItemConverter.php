<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Converter;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use DateTime;

class EzLocationItemConverter implements ConverterInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    protected $config = array();

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param array $config
     */
    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper,
        array $config
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
        $this->config = $config;
    }

    public function getId($valueObject)
    {
        return $valueObject->id;
    }

    public function getParentId($valueObject)
    {
        return $valueObject->parentLocationId != 1 ? $valueObject->parentLocationId : null;
    }

    public function getValue($valueObject)
    {
        return $valueObject->id;
    }

    public function getName($valueObject)
    {
        return $this->translationHelper->getTranslatedContentNameByContentInfo(
            $valueObject->contentInfo
        );
    }

    public function getIsSelectable($valueObject)
    {
        if (empty($this->config['types'])) {
            return true;
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $valueObject->contentInfo->contentTypeId
        );

        return in_array($contentType->identifier, $this->config['types']);
    }

    public function getTemplateVariables($valueObject)
    {
        return array(
            'content' => $this->repository->getContentService()->loadContentByContentInfo(
                $valueObject->contentInfo
            ),
            'location' => $valueObject,
        );
    }

    public function getColumns($valueObject)
    {
        $ownerContentInfo = $this->repository->sudo(
            function (Repository $repository) use ($valueObject) {
                return $repository->getContentService()->loadContentInfo(
                    $valueObject->contentInfo->ownerId
                );
            }
        );

        return array(
            'location_id' => $valueObject->id,
            'content_id' => $valueObject->contentId,
            'type' => $this->translationHelper->getTranslatedByMethod(
                $this->repository->getContentTypeService()->loadContentType(
                    $valueObject->contentInfo->contentTypeId
                ),
                'getName'
            ),
            'visible' => !$valueObject->invisible,
            'owner' => $this->translationHelper->getTranslatedContentNameByContentInfo(
                $ownerContentInfo
            ),
            'modified' => $valueObject->contentInfo->modificationDate->format(Datetime::ISO8601),
            'published' => $valueObject->contentInfo->publishedDate->format(Datetime::ISO8601),
            'priority' => $valueObject->priority,
            'section' => $this->repository->getSectionService()->loadSection(
                $valueObject->contentInfo->sectionId
            )->name,
        );
    }
}
