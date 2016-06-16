<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class Visible implements ColumnValueProviderInterface
{
    /**
     * Provides the column value.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface $value
     *
     * @return mixed
     */
    public function getValue(ValueInterface $value)
    {
        return $value->getValueObject()->invisible ? 'No' : 'Yes';
    }
}
