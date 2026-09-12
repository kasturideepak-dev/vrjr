<?php
declare(strict_types=1);

final class Fields
{
    public static function types(): array
    {
        return [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'richtext' => 'Rich text / WYSIWYG',
            'image' => 'Image',
            'gallery' => 'Gallery',
            'number' => 'Number',
            'date' => 'Date',
            'select' => 'Dropdown / select',
            'checkbox' => 'Checkbox',
            'url' => 'URL',
            'email' => 'Email',
            'repeater' => 'Repeater',
        ];
    }

    public static function formFieldTypes(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'tel' => 'Phone',
            'textarea' => 'Textarea',
            'select' => 'Dropdown',
            'checkbox' => 'Checkbox',
            'number' => 'Number',
            'date' => 'Date',
            'hidden' => 'Hidden',
        ];
    }
}
