<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;
use Netgen\Bundle\ContentBrowserBundle\Value\ValueInterface;

class Modified implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * Constructor.
     *
     * @param string $dateFormat
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
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
        return $value->getValueObject()->contentInfo->modificationDate->format(
            $this->dateFormat
        );
    }
}
