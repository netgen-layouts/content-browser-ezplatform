<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config;

class EzTagsFieldDefinitionConfigLoader extends FieldDefinitionConfigLoader
{
    const FIELD_TYPE_IDENTIFIER = 'eztags';

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
            'item_type' => 'eztags',
            'root_items' => array($fieldDefinition->fieldSettings['subTreeLimit']),
            'max_selected' => $fieldDefinition->fieldSettings['maxTags'],
        );
    }
}
