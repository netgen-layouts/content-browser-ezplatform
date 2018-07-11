<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
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

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzPublishInterface) {
            return null;
        }

        return $this->repository->sudo(
            function (Repository $repository) use ($item): string {
                if (trait_exists(MultiLanguageNameTrait::class)) {
                    try {
                        $ownerVersionInfo = $repository->getContentService()->loadVersionInfoById(
                            $item->getContent()->contentInfo->ownerId
                        );
                    } catch (NotFoundException $e) {
                        // Owner might be deleted in eZ database
                        return '';
                    }

                    return $ownerVersionInfo->getName() ?? '';
                }

                // @deprecated BC layer for getting content name in eZ Publish 5
                // Remove when support for eZ Publish 5 ends

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
