<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\Column\ColumnValueProvider\EzTags;

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
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $valueObject
     *
     * @return mixed
     */
    public function getValue($valueObject)
    {
        if ($valueObject->id > 0) {
            return $valueObject->modificationDate->format($this->dateFormat);
        }

        return '';
    }
}
