<?php

/**
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * A basic HTML View helper, with some self explainatory functions
 *
 * @author  Lars Kristian Dahl <http://www.krisd.com>
 */
class Html {

    private static $_model;

    private function __construct() {
        
    }

    public static function injectModel($model) {
        self::$_model = $model;
    }

    public static function e($value) {
        return self::chars($value);
    }

    public static function chars($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES);
    }

    public static function entities($value) {
        return htmlentities((string) $value, ENT_QUOTES);
    }

    public static function formInput($name, $value = NULL, array $attributes = NULL) {
        // Default type is text
        if (!isset($attributes['type'])) {
            $attributes['type'] = 'text';
        }

        // Set the input name
        $attributes['name'] = "form[$name]";

        // Set the input value
        if (isset(self::$_model->$name) && $attributes['type'] != 'password') {
            $value = self::$_model->$name;
        }

        $attributes['value'] = $value;

        return '<input' . self::attributes($attributes) . '/>';
    }

    public static function fieldset() {

    }

    public static function form($method='POST', $action=null, $attributes = NULL) {
        return '<form method="' . $method . '" action="' . $action . '" ' . self::attributes($attributes) . '>';
    }

    public static function formButton($name, $body, array $attributes = NULL) {
        // Set the input name
        $attributes['name'] = $name;
        return '<button' . self::attributes($attributes) . '>' . $body . '</button>';
    }

    public static function formCheckbox($name, $value = NULL, $checked = FALSE, array $attributes = NULL) {
        $attributes['type'] = 'checkbox';
        if ($checked === TRUE) {
            // Make the checkbox active
            $attributes['checked'] = 'checked';
        }
        return self::formInput($name, $value, $attributes);
    }

    public static function formEmail($name, $value = NULL, array $attributes = NULL) {
        $attributes['type'] = 'email';
        return self::formInput($name, $value, $attributes);
    }

    public static function formEnd() {
        return '</form>';
    }

    public static function formErrors() {
        $errors = self::$_model->getErrors();
        $return;
        if ($errors) {
            $return = '<div class="error"><span class="bold">Please fix the following input errors:</span><ul>';
            foreach ($errors as $error) {
                $return .= '<li>' . $error . '</li>';
            }
            $return .= '</ul></div>';
            return $return;
        }
    }

    public static function formFile($name, array $attributes = NULL) {
        $attributes['type'] = 'file';
        return self::formInput($name, NULL, $attributes);
    }

    public static function formHidden($name, $value = NULL, array $attributes = NULL) {
        $attributes['type'] = 'hidden';
        return self::formInput($name, $value, $attributes);
    }

    public static function formLabel($input, $text = NULL, array $attributes = NULL) {
        if ($text === NULL) {
            // Use the input name as the text
            $text = ucwords(preg_replace('/[\W_]+/', ' ', $input));
        }

        // Set the label target
        $attributes['for'] = 'form[' . $input . ']';

        return '<label' . self::attributes($attributes) . '>' . $text . '</label>';
    }

    public static function formPassword($name, $value = NULL, array $attributes = NULL) {
        $attributes['type'] = 'password';
        return self::formInput($name, $value, $attributes);
    }

    public static function formRadio($name, $value, array $attributes = NULL) {
        $attributes['type'] = 'radio';
        $attributes['name'] = 'form['.$name.']';
        $attributes['value'] = $value;
        if (self::$_model->Template == $value)
            $attributes['checked'] = 'checked';
        return '<input' . self::attributes($attributes) . ' />';
    }

    public static function formReset() {

    }

    public static function formSelect() {

    }

    public static function formSubmit($name, $value, array $attributes = NULL) {
        $attributes['type'] = 'submit';
        $attributes['name'] = $name;
        $attributes['value'] = $value;
        return '<input' . self::attributes($attributes) . ' />';
    }

    public static function formTextarea($name, $body = '', array $attributes = NULL, $double_encode = TRUE) {
        // Set the input name
        $attributes['name'] = "form[$name]";

        // Add default rows and cols attributes (required)
        $attributes += array('rows' => 10, 'cols' => 50);

        // If body is empty and text has been posted, place text in body
        if (!$body && isset(self::$_model->$name)) {
            $body = self::$_model->$name;
        }

        return '<textarea' . self::attributes($attributes) . '>' . self::chars($body, $double_encode) . '</textarea>';
    }

    public static function formUrl($name, $value = NULL, array $attributes = NULL) {
        $attributes['type'] = 'url';
        return self::formInput($name, $value, $attributes);
    }

    public static function url($url, $text) {
        return '<a href="' . $url . '">' . $text . '</a>';
    }

    public static function link($url, $text) {
        return '<a href="' . __BASE_URL . '/' . $url . '">' . $text . '</a>';
    }

    public static function menulink($url, $text) {
        return '<li><span class="menuitem"><a href="' . __BASE_URL . '/' . $url . '">' . $text . '</a></span></li>';
    }

    public static function htmlList() {

    }

    public static function image($file, array $attributes = NULL, $index = FALSE) {
        if (strpos($file, '://') === FALSE) {
            // Add the base URL
            $file = __BASE_URL . $file;
        }

        // Add the image link
        $attributes['src'] = $file;

        return '<img' . self::attributes($attributes) . ' />';
    }

    public static function profileimg($file, array $attributes = NULL) {
        $file = __BASE_URL . '/' . Config::get('profileimg_path') . '/' . $file . '.jpg';
        $attributes['src'] = $file;
        return '<img' . self::attributes($attributes) . ' />';
    }

    public static function style($file, array $attributes = NULL, $index = FALSE) {
        if (strpos($file, '://') === FALSE) {
            // Add the base URL
            $file = __BASE_URL . $file;
        }

        // Set the stylesheet link
        $attributes['href'] = $file;

        // Set the stylesheet rel
        $attributes['rel'] = 'stylesheet';

        // Set the stylesheet type
        $attributes['type'] = 'text/css';

        return '<link' . self::attributes($attributes) . ' />';
    }

    public static function mailto($email, $title = NULL, array $attributes = NULL) {
        // Obfuscate email address
        $email = self::email($email);

        if ($title === NULL) {
            // Use the email address as the title
            $title = $email;
        }

        return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;' . $email . '"' . self::attributes($attributes) . '>' . $title . '</a>';
    }

    private static function attributes(array $attributes = NULL) {
        if (empty($attributes))
            return '';
        $compiled = '';
        foreach ($attributes as $key => $value) {
            if ($value === NULL) {
                // Skip attributes that have NULL values
                continue;
            }

            if (is_int($key)) {
                // Assume non-associative keys are mirrored attributes
                $key = $value;
            }

            // Add the attribute value
            $compiled .= ' ' . $key . '="' . self::chars($value) . '"';
        }

        return $compiled;
    }

}