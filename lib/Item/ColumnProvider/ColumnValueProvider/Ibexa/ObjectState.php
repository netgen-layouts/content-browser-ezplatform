<?php

declare(strict_types=1);

namespace Netgen\ContentBrowser\Ibexa\Item\ColumnProvider\ColumnValueProvider\Ibexa;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState as IbexaObjectState;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\ContentBrowser\Ibexa\Item\Ibexa\IbexaInterface;
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
        if (!$item instanceof IbexaInterface) {
            return null;
        }

        $groups = $this->repository->sudo(
            static fn (Repository $repository): iterable => $repository
                ->getObjectStateService()->loadObjectStateGroups(),
        );

        $states = array_map(
            fn (ObjectStateGroup $group): IbexaObjectState => $this->repository->sudo(
                static fn (Repository $repository): IbexaObjectState => $repository
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
                static fn (IbexaObjectState $state): string => $state->getName() ?? '',
                $states,
            ),
        );
    }
}
