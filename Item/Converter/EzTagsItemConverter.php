<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Converter;

use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\TagsBundle\API\Repository\TagsService;
use DateTime;

class EzTagsItemConverter implements ConverterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Returns the ID of the value object.
     *
     * @param mixed $valueObject
     *
     * @return int|string
     */
    public function getId($valueObject)
    {
        return $valueObject->id;
    }

    /**
     * Returns the parent ID of the value object.
     *
     * @param mixed $valueObject
     *
     * @return int|string
     */
    public function getParentId($valueObject)
    {
        return $valueObject->parentTagId;
    }

    /**
     * Returns the value of the value object.
     *
     * @param mixed $valueObject
     *
     * @return int|string
     */
    public function getValue($valueObject)
    {
        return $valueObject->id;
    }

    /**
     * Returns the name of the value object.
     *
     * @param mixed $valueObject
     *
     * @return string
     */
    public function getName($valueObject)
    {
        return $this->translationHelper->getTranslatedByMethod(
            $valueObject,
            'getKeyword'
        );
    }

    /**
     * Returns the selectable flag of the value object.
     *
     * @param mixed $valueObject
     *
     * @return bool
     */
    public function getIsSelectable($valueObject)
    {
        return $valueObject->id > 0 ? true : false;
    }

    /**
     * Returns the template variables of the value object.
     *
     * @param mixed $valueObject
     *
     * @return array
     */
    public function getTemplateVariables($valueObject)
    {
        return array(
            'tag' => $valueObject->id > 0 ? $valueObject : null,
        );
    }

    /**
     * Returns the columns of the value object.
     *
     * @param mixed $valueObject
     *
     * @return array
     */
    public function getColumns($valueObject)
    {
        $parentTagName = $this->tagsService->sudo(
            function (TagsService $tagsService) use ($valueObject) {
                if (empty($valueObject->parentTagId)) {
                    return '(No parent)';
                }

                return $this->translationHelper->getTranslatedByMethod(
                    $tagsService->loadTag($valueObject->parentTagId),
                    'getKeyword'
                );
            }
        );

        if ($valueObject->id > 0) {
            return array(
                'tag_id' => $valueObject->id,
                'parent_tag_id' => $valueObject->parentTagId,
                'parent_tag' => $parentTagName,
                'modified' => $valueObject->modificationDate->format(Datetime::ISO8601),
            );
        }

        return array();
    }
}
