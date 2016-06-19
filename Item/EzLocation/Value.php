<?php

namespace Netgen\Bundle\ContentBrowserBundle\Item\EzLocation;

use eZ\Publish\API\Repository\Values\Content\Location;
use Netgen\Bundle\ContentBrowserBundle\Item\ValueInterface;

class Value implements ValueInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected $location;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $name
     */
    public function __construct(Location $location, $name)
    {
        $this->location = $location;
        $this->name = $name;
    }

    /**
     * Returns the value ID.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->location->id;
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
     * Returns the value object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getValueObject()
    {
        return $this->location;
    }
}
