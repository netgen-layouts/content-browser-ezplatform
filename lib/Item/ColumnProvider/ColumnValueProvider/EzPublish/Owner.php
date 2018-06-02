<?php

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Owner implements ColumnValueProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    public function __construct(Repository $repository, TranslationHelper $translationHelper)
    {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EzPublishInterface) {
            return null;
        }

        return $this->repository->sudo(
            function (Repository $repository) use ($item) {
                try {
                    $ownerContentInfo = $repository->getContentService()->loadContentInfo(
                        $item->getContent()->contentInfo->ownerId
                    );
                } catch (NotFoundException $e) {
                    // Owner might be deleted in eZ database
                    return '';
                }

                return $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $ownerContentInfo
                );
            }
        );
    }
}
