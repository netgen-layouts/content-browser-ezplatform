<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition;

class RelationListConfigLoader extends ConfigLoader
{
    const FIELD_TYPE_IDENTIFIER = 'ezobjectrelationlist';

    /**
     * Returns the item type which this config supports.
     *
     * @return string
     */
    public function getItemType()
    {
        return 'ezcontent';
    }

    /**
     * Loads the configuration by its name.
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
            'types' => $fieldDefinition->fieldSettings['selectionContentTypes'],
        );
    }
}
