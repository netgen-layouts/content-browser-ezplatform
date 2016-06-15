<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzPublish;

use Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProviderInterface;

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
     * @param \eZ\Publish\API\Repository\Values\Content\Location $valueObject
     *
     * @return mixed
     */
    public function getValue($valueObject)
    {
        return $valueObject->contentInfo->modificationDate->format($this->dateFormat);
    }
}
