<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Converter;

use eZ\Publish\Core\Helper\TranslationHelper;

class EzTagsItemConverter implements ConverterInterface
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    public function getId($valueObject)
    {
        return $valueObject->id;
    }

    public function getParentId($valueObject)
    {
        return $valueObject->parentTagId != 0 ? $valueObject->parentTagId : null;
    }

    public function getValue($valueObject)
    {
        return $valueObject->id;
    }

    public function getName($valueObject)
    {
        return $this->translationHelper->getTranslatedByMethod(
            $valueObject,
            'getKeyword'
        );
    }

    public function getIsSelectable($valueObject)
    {
        return true;
    }

    public function getTemplateVariables($valueObject)
    {
        return array(
            'tag' => $valueObject,
        );
    }

    public function getColumns($valueObject)
    {
        return array(
            'tag_id' => $valueObject->id,
        );
    }
}
