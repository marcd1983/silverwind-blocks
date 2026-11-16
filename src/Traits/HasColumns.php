<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;

/**
 * Items per row on phones / tablets / desktops, for the grid-layout Section and the
 * Gallery. Class names are listed in full so Tailwind can find them.
 */
trait HasColumns
{
    public const array COLUMNS_DB = [
        'ColumnsSmall' => 'Int',
        'ColumnsMedium' => 'Int',
        'ColumnsLarge' => 'Int',
    ];

    private const array COLUMN_LIMITS = ['ColumnsSmall' => 3, 'ColumnsMedium' => 4, 'ColumnsLarge' => 6];

    private const array COLUMN_CLASSES = [
        'grid' => [
            'ColumnsSmall' => [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3'],
            'ColumnsMedium' => [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'],
            'ColumnsLarge' => [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5', 6 => 'lg:grid-cols-6'],
        ],
        'columns' => [
            'ColumnsSmall' => [1 => 'columns-1', 2 => 'columns-2', 3 => 'columns-3'],
            'ColumnsMedium' => [1 => 'md:columns-1', 2 => 'md:columns-2', 3 => 'md:columns-3', 4 => 'md:columns-4'],
            'ColumnsLarge' => [1 => 'lg:columns-1', 2 => 'lg:columns-2', 3 => 'lg:columns-3', 4 => 'lg:columns-4', 5 => 'lg:columns-5', 6 => 'lg:columns-6'],
        ],
    ];

    private const array COLUMN_FALLBACK = ['ColumnsSmall' => 1, 'ColumnsMedium' => 2, 'ColumnsLarge' => 3];

    protected function columnsField(string $title): FieldGroup
    {
        $range = fn (int $max) => array_combine(range(1, $max), range(1, $max));

        return FieldGroup::create($title, [
            DropdownField::create('ColumnsSmall', _t(self::class . '.Phones', 'Phones'), $range(self::COLUMN_LIMITS['ColumnsSmall'])),
            DropdownField::create('ColumnsMedium', _t(self::class . '.Tablets', 'Tablets'), $range(self::COLUMN_LIMITS['ColumnsMedium'])),
            DropdownField::create('ColumnsLarge', _t(self::class . '.Desktops', 'Desktops'), $range(self::COLUMN_LIMITS['ColumnsLarge'])),
        ])->setName('Columns');
    }

    /**
     * @param string $kind 'grid' (CSS grid) or 'columns' (CSS multi-column)
     */
    protected function columnClasses(string $kind): string
    {
        $classes = [];
        foreach (self::COLUMN_CLASSES[$kind] as $field => $map) {
            $classes[] = $map[(int) $this->getField($field)] ?? $map[self::COLUMN_FALLBACK[$field]];
        }

        return implode(' ', $classes);
    }
}
