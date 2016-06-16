<?php

namespace Netgen\Bundle\ContentBrowserBundle\Value\Loader;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Value\EzTags;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueLoaderInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class EzTagsValueLoader implements ValueLoaderInterface
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
     * Loads the value by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function load($id)
    {
        if ($id > 0) {
            try {
                $tag = $this->tagsService->loadTag($id);
            } catch (APINotFoundException $e) {
                throw new NotFoundException(
                    sprintf(
                        'Value of type "%s" with "%s" ID not found.',
                        $this->getValueType(),
                        $id
                    )
                );
            }
        } else {
            $tag = new Tag(
                array(
                    'id' => 0,
                    'parentTagId' => null,
                    'keywords' => array(
                        'eng-GB' => 'All tags',
                    ),
                    'mainLanguageCode' => 'eng-GB',
                    'alwaysAvailable' => true,
                )
            );
        }

        return $this->buildValue($tag);
    }

    /**
     * Loads the value by its internal value.
     *
     * @param int|string $value
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function loadByValue($value)
    {
        return $this->load($value);
    }

    /**
     * Builds the value from provided value object.
     *
     * @param mixed $valueObject
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface
     */
    public function buildValue($valueObject)
    {
        return new EzTags(
            $valueObject,
            $this->translationHelper->getTranslatedByMethod(
                $valueObject,
                'getKeyword'
            )
        );
    }
}
