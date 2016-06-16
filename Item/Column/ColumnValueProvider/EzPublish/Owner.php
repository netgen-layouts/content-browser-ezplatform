<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class Owner implements ColumnValueProviderInterface
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
    public function __construct(Repository $repository, TranslationHelper $translationHelper)
    {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Provides the column value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return mixed
     */
    public function getValue(ValueInterface $value)
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($value) {
                return $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $value->getValueObject()->contentInfo
                );
            }
        );
    }
}
