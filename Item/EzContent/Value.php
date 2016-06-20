<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzContent;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Netgen\Bundle\ContentBrowserBundle\Item\ValueInterface;

class Value implements ValueInterface, EzContentInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $name
     */
    public function __construct(ContentInfo $contentInfo, $name)
    {
        $this->contentInfo = $contentInfo;
        $this->name = $name;
    }

    /**
     * Returns the value ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->contentInfo->id;
    }

    /**
     * Returns the value name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the content info.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }
}
