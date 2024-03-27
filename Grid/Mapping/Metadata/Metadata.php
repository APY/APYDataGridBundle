<?php

namespace APY\DataGridBundle\Grid\Mapping\Metadata;

use APY\DataGridBundle\Grid\Columns;

class Metadata
{
    protected ?string $name = null;
    protected ?array $fields = null;
    protected ?array $fieldsMappings = null;
    protected ?array $groupBy = null;

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function setFieldsMappings(array $fieldsMappings): static
    {
        $this->fieldsMappings = $fieldsMappings;

        return $this;
    }

    public function hasFieldMapping(string $field): bool
    {
        return isset($this->fieldsMappings[$field]);
    }

    public function getFieldMapping(string $field): mixed
    {
        return $this->fieldsMappings[$field];
    }

    public function getFieldMappingType(string $field): string
    {
        return $this->fieldsMappings[$field]['type'] ?? 'text';
    }

    public function setGroupBy(array $groupBy): static
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function getGroupBy(): ?array
    {
        return $this->groupBy;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @todo move to another place
     *
     * @throws \RuntimeException
     */
    public function getColumnsFromMapping(Columns $columnExtensions): \SplObjectStorage
    {
        $columns = new \SplObjectStorage();

        foreach ($this->getFields() as $value) {
            $params = $this->getFieldMapping($value);
            $type = $this->getFieldMappingType($value);

            // todo move available extensions from columns
            if ($columnExtensions->hasExtensionForColumnType($type)) {
                $column = clone $columnExtensions->getExtensionForColumnType($type);
                $column->__initialize($params);
                $columns->attach($column);
            } else {
                throw new \RuntimeException(\sprintf('No suitable Column Extension found for column type: %s', $type));
            }
        }

        return $columns;
    }
}
