<?php

class SmartFormGenerator {

    protected static $formPath = 'forms';

    protected static $formExtension = '.form';

    protected static $keywordDelimiter = '@';

    protected static $valueDelimiter = '=';

    /**
     * Generates a simple but cms compliant form template (Blad syntax).
     * Do not blindly trust the result - most like rework is necessary.
     * @param  string $tableName  The table (representing a model) to generate a form for
     * @param  string $moduleName The module name - leave it empty if its the pluralized model name
     * @return string
     */
    public static function generate($tableName, $moduleName = NULL)
    {

        if ($moduleName == NULL) $moduleName = $tableName;

        $columns = DB::select('SHOW COLUMNS FROM '.$tableName);

        $fields = array();
        foreach ($columns as $columndIndex => $column) {
            $field = self::buildField($column);
            if ($field) $fields[] = $field;
        }

        $formView = View::make('smartform.template', ['fields' => $fields, 'modulename' => $moduleName]);

        return $formView->render();
    }

    /**
     * Creates a single form field.
     * Returns NULL if the field is ignored.
     * @param  stdClass $column The Column object
     * @return $string
     */
    protected static function buildField($column)
    {
        $ignoredFields = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

        $name       = strtolower($column->Field);
        $title      = ucfirst($name);
        $type       = strtolower($column->Type);
        $meta       = '';
        $size       = 0;
        $required   = (strtolower($column->Null) == 'no');
        $default    = $column->Default;

        if (str_contains($type, '(')) {
            $pos    = strpos($type, '(');
            $meta   = substr($type, $pos);
            $type   = substr($type, 0, $pos);
        }

        if (starts_with($meta, '(')) {
            $size   = (int) substr($meta, 1);
            $pos    = strpos($meta, ')');
            $meta   = trim(substr($meta, $pos + 1));
        }

        $html = NULL;
        if (! in_array($column->Field, $ignoredFields)) {
            if ($name == 'image') $type = 'image';
            if ($name == 'email') $type = 'email';
            if ($name == 'password') $type = 'password';
            if (ends_with($name, '_id')) $type = 'foreign';

            $attributes = [];
            if ($size > 0) $attributes['maxlength'] = $size;
            if ($required) $attributes['required'] = 'required';

            switch ($type) {
                case 'tinyint':
                    $html = Form::label($name, $title)."\n".Form::checkbox($name, 1, $default)."\n";
                    break;
                case 'int':
                    unset($attributes['maxlength']);
                    $attributes['class'] = 'numeric';
                    $html = Form::label($name, $title)."\n".Form::text($name, $default, $attributes)."\n";
                    break;
                case 'varchar':
                    $html = Form::label($name, $title)."\n".Form::text($name, $default, $attributes)."\n";
                    break;
                case 'email':
                    $html = Form::label($name, $title)."\n".Form::email($name, $default, $attributes)."\n";
                    break;
                case 'password':
                    $html = Form::label($name, $title)."\n".Form::password($name, $attributes)."\n";
                    break;
                case 'text':
                    $html = Form::label($name, $title)."\n".Form::textarea($name, NULL, $attributes)."\n";
                    break;
                case 'timestamp':
                    if ($size > 0)
                    $html = Form::label($name, $title)."\n".Form::text($name, $default, ['class' => 'timestamp'])."\n";
                    break;
                case 'image':
                    unset($attributes['maxlength']);
                    $html = Form::label($name, $title)."\n".Form::file($name, $attributes)."\n";
                    break;
                case 'foreign':
                    $html = Form::label($name, $title)."\n".Form::select($name, array(), NULL, $attributes)."\n";
                    break;
                default:
                    $html = '<!-- Unknown type: '.$type.' -->';
                    break;
            }
        }

        return $html;
    }

    /*
    public static function compile($formName)
    {
        $formOutput = '';
        $fileName   = NULL;

        if (str_contains($formName, '::')) {
            $parts = explode('::', $formName);
            $fileName = 'modules/'.$parts[0].'/'.self::$formPath.'/'.$parts[1].self::$formExtension;
        } else {
            if (! str_contains($formName, '/')) {
                $fileName = self::$formPath.'/'.str_replace('.', '/', $formName).self::$formExtension;
            }
        }

        $fileName = app_path().'/'.$fileName;
        if (File::isFile($fileName)) {
            $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $lineNumber => $line) {
                $parts = explode(self::$keywordDelimiter, $line);
                unset($parts[0]);
                $parts = array_values($parts);

                $method = 'partial'.$parts[0];
                unset($parts[0]);
                $partial = self::$method(array_values($parts));

                $formOutput .= $partial."\n";
            }

            $formOutput = View::make('sfb.header')->render()."\n".$formOutput;

            return $formOutput;
        } else {
            throw new Exception('The given form does not exist.');
        }
    }

    protected static function partialErrors($params)
    {
        $partial = View::make('sfb.errors');
        return $partial->render();
    }

    public static function __callStatic($method, $args)
    {
        if (starts_with($method, 'partial')) {
            $method = substr($method, strlen('partial'));
            throw new Exception('Error in form: Unkown keyword '.$method);
        }
    }
    */
}