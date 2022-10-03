<?php

namespace Adetxt\Migraml;

class Migraml
{
    const DROP_INDEX = 'drop_index';

    public function __construct(
        protected array $data
    ) {
    }

    public function parseActions(string $type)
    {
        $actions = $this->data[$type];

        $result = [];

        foreach ($actions as $action) {
            $result[] = [
                'action' => $action['action'],
                'table' => $action['table'],
                'columns' => isset($action['columns']) ? collect($action['columns'])
                    ->map(fn ($setup, $name) => $this->parseColumn($name, $setup))
                    ->toArray() : null,
                'indexes' => $this->parseIndexes($action),
            ];
        }

        return $result;
    }

    private function parseColumn(string $name, ?string $column): string
    {
        if ($column === 'id') {
            return '$table->id()';
        } else if ($column === 'timestamps') {
            return '$table->timestamps()';
        } else if ($column === 'softDeletes') {
            return '$table->softDeletes()';
        }

        $parts = explode('.', $column);

        $type = empty($parts[0]) ? 'string' : $parts[0];
        $length = $this->getcolumnLength($type);
        $type = preg_replace('/\(.*\)/', '', $type);

        $res = '$table->' . $type . '(\'' . $name . ($length ? '\', ' . $length : '\'') . ')';

        if (count($parts) > 1) {
            unset($parts[0]);

            $parts = collect($parts)->map(function ($part) {
                if (str($part)->contains(['(', ')'])) {
                    return $part;
                }

                return $part . '()';
            })->toArray();

            $res .= '->' . implode('->', $parts);
        }

        return $res;
    }

    private function getcolumnLength(string $columnType): ?int
    {
        if (str($columnType)->contains(['(', ')'])) {
            return (int) str($columnType)->between('(', ')')->toString();
        }

        return null;
    }

    private function parseIndexes(array $action): array
    {
        if (!isset($action['indexes']) || empty($action['indexes'])) {
            return [];
        }

        $down = false;
        if ($action['action'] === self::DROP_INDEX) {
            $down = true;
        }

        return collect($action['indexes'])->map(fn ($i) => $this->parseIndex($i, $down))->toArray();
    }

    private function parseIndex(string $index, bool $down = false): string
    {
        $parts = explode('.', $index);

        $columns = explode(',', $parts[0]);
        $name = $parts[1] ?? null;

        $columns = collect($columns)->map(fn ($i) => "'" . $i . "'")->implode(',');

        if ($down) {
            return '$table->dropIndex([' . $columns . '])';
        } else {
            $res = '$table->index([' . $columns . ']';

            if ($name) {
                $res .= ', \'' . $name . '\'';
            }

            $res .= ')';
        }

        return $res;
    }
}
