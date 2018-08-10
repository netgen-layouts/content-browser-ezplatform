<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
use Netgen\ContentBrowser\Ez\Item\EzPublish\EzPublishInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class ContentType implements ColumnValueProviderInterface
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
                $contentType = $repository->getContentTypeService()->loadContentType(
                    $item->getContent()->contentInfo->contentTypeId
                );

                if (trait_exists(MultiLanguageNameTrait::class)) {
                    return $contentType->getName() ?? '';
                }

                // @deprecated BC layer for getting content type name in eZ Publish 5
                // Remove when support for eZ Publish 5 ends

                return (string) $this->translationHelper->getTranslatedByMethod($contentType, 'getName');
            }
        );
    }
}
