<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tree\EzTags;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\Bundle\ContentBrowserBundle\Tree\Location;
use Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Netgen\Bundle\ContentBrowserBundle\Tree\AdapterInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use DateTime;

class Adapter implements AdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Netgen\Bundle\ContentBrowserBundle\Tree\EzTags\LocationBuilder
     */
    protected $locationBuilder;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\EzTags\LocationBuilder $locationBuilder
     */
    public function __construct(
        TagsService $tagsService,
        LocationBuilder $locationBuilder
    ) {
        $this->tagsService = $tagsService;
        $this->locationBuilder = $locationBuilder;
    }

    /**
     * Returns all available columns and their names
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'modified' => 'netgen_content_browser.eztags.columns.modified',
            'published' => 'netgen_content_browser.eztags.columns.published',
        );
    }

    /**
     * Loads the location for provided ID.
     *
     * @param int|string $locationId
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\NotFoundException If location with provided ID was not found
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Tree\Location
     */
    public function loadLocation($locationId)
    {
        // Tags have no root location, so we simulate it with location ID == 0
        if ($locationId == 0) {
            return $this->locationBuilder->buildLocation(
                new Tag(
                    array(
                        'id' => 0,
                        'parentTagId' => null,
                        'pathString' => '/0/',
                        'modificationDate' => new DateTime("now"),
                        'mainLanguageCode' => 'eng-GB',
                        'keywords' => array(
                            'eng-GB' => 'Tags'
                        )
                    )
                )
            );
        }

        try {
            $tag = $this->tagsService->loadTag($locationId);
        } catch (APINotFoundException $e) {
            throw new NotFoundException("Location #{$locationId} not found.", 0, $e);
        }

        return $this->locationBuilder->buildLocation($tag);
    }

    /**
     * Loads all children of the provided location.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\Location $location
     * @param string[] $types
     *
     * @return \Netgen\Bundle\ContentBrowserBundle\Tree\Location[]
     */
    public function loadLocationChildren(Location $location, array $types = array())
    {
        $tag = null;
        if ($location->id > 0) {
            $tag = $this->tagsService->loadTag($location->id);
        }

        $locations = array_map(
            function (Tag $tag) {
                return $this->locationBuilder->buildLocation(
                    $tag
                );
            },
            $this->tagsService->loadTagChildren($tag)
        );

        return $locations;
    }

    /**
     * Returns true if provided location has children.
     *
     * @param \Netgen\Bundle\ContentBrowserBundle\Tree\Location $location
     * @param string[] $types
     *
     * @return bool
     */
    public function hasChildren(Location $location, array $types = array())
    {
        $tag = null;
        if ($location->id > 0) {
            $tag = $this->tagsService->loadTag($location->id);
        }

        return $this->tagsService->getTagChildrenCount($tag) > 0;
    }
}
