<?php

namespace Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use Netgen\Bundle\ContentBrowserBundle\Repository\LocationInterface;
use Netgen\Bundle\ContentBrowserBundle\Repository\EzPublish\LocationInterface as EzPublishLocationInterface;

class Location implements LocationInterface, EzPublishLocationInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var string
     */
    protected $contentName;

    /**
     * @var string
     */
    protected $contentTypeName;

    /**
     * @var bool
     */
    protected $hasChildren = false;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $contentName
     * @param string $contentTypeName
     */
    public function __construct(APILocation $location, $contentName, $contentTypeName)
    {
        $this->location = $location;
        $this->contentName = $contentName;
        $this->contentTypeName = $contentTypeName;
    }

    /**
     * Returns location ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->location->id;
    }

    /**
     * Returns location parent ID.
     *
     * @return int|string
     */
    public function getParentId()
    {
        return $this->location->parentLocationId;
    }

    /**
     * Returns location name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->contentName;
    }

    /**
     * Returns if location can be selected or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Returns location thumbnail.
     *
     * @return string
     */
    public function getThumbnail()
    {
        return null;
    }

    /**
     * Returns location type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->contentTypeName;
    }

    /**
     * Returns if location is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return !$this->location->invisible;
    }

    /**
     * Returns the API location.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getAPILocation()
    {
        return $this->location;
    }
}
