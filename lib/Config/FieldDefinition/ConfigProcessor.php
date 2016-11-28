<?php

namespace Netgen\ContentBrowser\Config\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Netgen\ContentBrowser\Config\ConfigProcessorInterface;
use Netgen\ContentBrowser\Exceptions\InvalidArgumentException;

abstract class ConfigProcessor implements ConfigProcessorInterface
{
    const CONFIG_NAME_PREFIX = 'ez-field-definition-';

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns the field type identifier for this config processor.
     *
     * @return string
     */
    abstract public function getFieldTypeIdentifier();

    /**
     * Returns if the processor supports the config with provided name.
     *
     * @param string $configName
     *
     * @return bool
     */
    public function supports($configName)
    {
        return stripos(
            $configName,
            static::CONFIG_NAME_PREFIX . $this->getFieldTypeIdentifier() . '-'
        ) === 0;
    }

    /**
     * Returns the field definition from provided config name.
     *
     * @param string $configName
     *
     * @throws \Netgen\ContentBrowser\Exceptions\InvalidArgumentException If field definition could not be loaded
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    protected function getFieldDefinition($configName)
    {
        $configName = substr($configName, strlen(static::CONFIG_NAME_PREFIX));

        $configName = explode('-', $configName);
        if (!isset($configName[2])) {
            throw new InvalidArgumentException('Config name format for field definition is not valid.');
        }

        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($configName[1]);
        } catch (NotFoundException $e) {
            throw new InvalidArgumentException("'{$configName[1]}' content type does not exist.");
        }

        $fieldDefinition = $contentType->getFieldDefinition($configName[2]);
        if (!$fieldDefinition instanceof FieldDefinition) {
            throw new InvalidArgumentException("'{$configName[2]}' field definition does not exist.");
        }

        if ($fieldDefinition->fieldTypeIdentifier !== $configName[0]) {
            throw new InvalidArgumentException("'{$configName[2]}' field definition is not of '{$configName[0]}' field type.");
        }

        return $fieldDefinition;
    }
}
