<?php

namespace Netgen\ContentBrowser\Config\FieldDefinition;

class RelationListConfigProcessor extends ConfigProcessor
{
    /**
     * Returns the field type identifier for this config processor.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * Returns the item type which this config processor supports.
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
     * @param \Netgen\ContentBrowser\Config\ConfigurationInterface $config
     *
     * @throws \Netgen\ContentBrowser\Exceptions\InvalidArgumentException If config could not be found
     */
    public function processConfig($configName, $config)
    {
        $fieldDefinition = $this->getFieldDefinition($configName);

        $config->setParameter('types', $fieldDefinition->fieldSettings['selectionContentTypes']);
    }
}
