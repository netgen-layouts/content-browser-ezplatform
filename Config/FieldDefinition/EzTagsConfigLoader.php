<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition;

class EzTagsConfigLoader extends ConfigLoader
{
    /**
     * Returns the field type identifier for this config loader.
     *
     * @return string
     */
    protected function getFieldTypeIdentifier()
    {
        return 'eztags';
    }

    /**
     * Returns the item type which this config supports.
     *
     * @return string
     */
    public function getItemType()
    {
        return 'eztags';
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
            'root_items' => array($fieldDefinition->fieldSettings['subTreeLimit']),
            'max_selected' => $fieldDefinition->fieldSettings['maxTags'],
        );
    }
}
