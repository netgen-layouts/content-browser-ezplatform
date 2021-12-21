<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ez\Item\ColumnProvider\ColumnValueProvider\EzPlatform;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState as EzObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\ContentBrowser\Ez\Item\EzPlatform\EzPlatformInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use function array_map;
use function count;
use function implode;

final class ObjectState implements ColumnValueProviderInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EzPlatformInterface) {
            return null;
        }

        $groups = $this->repository->sudo(
            static fn (Repository $repository): iterable => $repository
                ->getObjectStateService()->loadObjectStateGroups(),
        );

        $states = array_map(
            fn (ObjectStateGroup $group): EzObjectState => $this->repository->sudo(
                static fn (Repository $repository): EzObjectState => $repository
                    ->getObjectStateService()
                    ->getContentState($item->getContent()->contentInfo, $group),
            ),
            $groups,
        );

        if (count($states) === 0) {
            return '';
        }

        return implode(
            ', ',
            array_map(
                static fn (EzObjectState $state): string => $state->getName() ?? '',
                $states,
            ),
        );
    }
}
