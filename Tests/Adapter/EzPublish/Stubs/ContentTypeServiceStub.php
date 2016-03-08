<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Adapter\EzPublish\Stubs;

use eZ\Publish\Core\Repository\Values\ContentType\ContentType;

class ContentTypeServiceStub
{
    public function loadContentType($contentTypeId)
    {
        return new ContentType(
            array(
                'fieldDefinitions' => array(),
            )
        );
    }
}
