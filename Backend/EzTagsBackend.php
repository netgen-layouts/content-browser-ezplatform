<?php

namespace Netgen\Bundle\ContentBrowserBundle\Backend;

use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;
use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Value;
use Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;

class EzTagsBackend implements BackendInterface
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
     * @var array
     */
    protected $languages;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param array $languages
     */
    public function __construct(TagsService $tagsService, TranslationHelper $translationHelper, array $languages)
    {
        $this->tagsService = $tagsService;
        $this->translationHelper = $translationHelper;
        $this->languages = $languages;
    }

    /**
     * Loads the item by its ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If item does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function load($id)
    {
        if ($id > 0) {
            try {
                $tag = $this->tagsService->loadTag($id);
            } catch (APINotFoundException $e) {
                throw new NotFoundException(
                    sprintf(
                        'Item with "%s" ID not found.',
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

        return $this->buildItems(array($tag))[0];
    }

    /**
     * Loads the item by its value ID.
     *
     * @param int|string $id
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If value does not exist
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface
     */
    public function loadByValue($id)
    {
        return $this->load($id);
    }

    /**
     * Returns the category children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubCategories(ItemInterface $item)
    {
        return $this->getSubItems($item, 0, -1);
    }

    /**
     * Returns the category children count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return int
     */
    public function getSubCategoriesCount(ItemInterface $item)
    {
        return $this->getSubItemsCount($item);
    }

    /**
     * Returns the item children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function getSubItems(ItemInterface $item, $offset = 0, $limit = 25)
    {
        $tags = $this->tagsService->loadTagChildren(
            !empty($item->getId()) ? $item->getValue()->getValueObject() : null,
            $offset,
            $limit
        );

        return $this->buildItems($tags);
    }

    /**
     * Returns the item children count.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface $item
     *
     * @return int
     */
    public function getSubItemsCount(ItemInterface $item)
    {
        return $this->tagsService->getTagChildrenCount(
            !empty($item->getId()) ? $item->getValue()->getValueObject() : null
        );
    }

    /**
     * Searches for items.
     *
     * @param string $searchText
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Item\ItemInterface[]
     */
    public function search($searchText, $offset = 0, $limit = 25)
    {
        if (empty($this->languages)) {
            return array();
        }

        $tags = $this->tagsService->loadTagsByKeyword(
            $searchText,
            $this->languages[0],
            true,
            $offset,
            $limit
        );

        return $this->buildItems($tags);
    }

    /**
     * Returns the count of searched items.
     *
     * @param string $searchText
     *
     * @return int
     */
    public function searchCount($searchText)
    {
        if (empty($this->languages)) {
            return 0;
        }

        return $this->tagsService->getTagsByKeywordCount(
            $searchText,
            $this->languages[0]
        );
    }

    /**
     * Builds the values from tags.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[] $tags
     *
     * @return array
     */
    protected function buildItems(array $tags)
    {
        return array_map(
            function (Tag $tag) {
                return new Item(
                    new Value(
                        $tag,
                        $this->translationHelper->getTranslatedByMethod(
                            $tag,
                            'getKeyword'
                        )
                    )
                );
            },
            $tags
        );
    }
}
