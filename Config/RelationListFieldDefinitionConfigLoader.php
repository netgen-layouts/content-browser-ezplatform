<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config;

class RelationListFieldDefinitionConfigLoader extends FieldDefinitionConfigLoader
{
    const FIELD_TYPE_IDENTIFIER = 'ezobjectrelationlist';

    /**
     * Loads the configuration by its name
     *
     * @param string $configName
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException If config could not be found
     *
     * @return array
     */
    public function loadConfig($configName)
    {
        $fieldDefinition = $this->getFieldDefinition($configName);

        return array(
            'item_type' => 'ezcontent',
            'types' => $fieldDefinition->fieldSettings['selectionContentTypes'],
        );
    }
}
