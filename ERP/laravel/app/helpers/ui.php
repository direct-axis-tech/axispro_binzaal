<?php

/**
 * Determines if application is running in dark mode
 *
 * @return boolean
 */
function in_dark_mode()
{
    return false;
}

/**
 * Determines if application is running in rtl mode
 * 
 * @return boolean
 */
function in_rtl_mode()
{
    return false;
}

/**
 * Check if the given key is the current active menu or not
 * 
 * @param string $key
 * @return boolean
 */
function is_active_menu($key)
{
    $currentUrl = url()->full();

    $criteria = [
        'sales'     => ['#/sales#', '#application=sales#'],
        'labour'     => ['#/labours#', '#application=labour#'],
        'purchase'  => ['#/(purchasing|purchase|inventory)#', '#application=purchase#'],
        'finance'   => ['#(/gl|_accounts)#', '#application=finance#'],
        'hrm'       => ['#/(hrm|hr)#', '#application=hr#'],
        'reports'   => ['#(_report|reports|rep_|profit_and_loss)#', '#application=reports#'],
        'system'    => ['#(/admin|settings|system)#', '#application=settings#'],
        'fixed_assets' => ['#(/fixed_assets|assets_allocation)#', '#application=fixed_assets#', '#FixedAsset=1#']
    ];

    $activeMenu = null;

    foreach ($criteria as $menu => $regExps) {
        $isMatching = false;

        foreach ($regExps as $regExp) {
            $isMatching = $isMatching || preg_match($regExp, $currentUrl);
        }

        if ($isMatching) {
            $activeMenu = $menu;
        }
    }

    if ($activeMenu == $key) {
        return true;
    }

    // Nothing matched so by default its dashboard.
    if ($activeMenu === null && $key == 'dashboard') {
        return true;
    }

    return false;
}

/**
 * Constructs the class names from the arguments
 *
 * @param  mixed $args
 * @return string
 */
function class_names($args)
{
    $classes = [];

    $args = func_get_args();
    foreach ($args as $arg) {
        if (!$arg) continue;

        if (gettype($arg) === 'string') {
            $classes[] = $arg;
        } else if (is_array($arg)) {
            foreach ($arg as $key => $value) {
                if (!$value) {
                    continue;
                }

                $keyType = gettype($key);

                if (gettype($value) == 'string' &&  $keyType == 'integer') {
                    $classes[] = $value;
                }
                
                if ($keyType == 'string') {
                    $classes[] = $key;
                }
            }
        }
    }

    return implode(" ", $classes);
}

/**
 * Generate the svg icon
 *
 * @param string $path
 * @param string|null $class
 * @param string|null $svgClass
 * @return string
 */
function get_svg_icon($path, $class = null, $svgClass = null)
{
    $file_path = media_path($path);

    if (!file_exists($file_path)) {
        return '';
    }

    $svg_content = file_get_contents($file_path);

    if (empty($svg_content)) {
        return '';
    }

    $dom = new DOMDocument();
    $dom->loadXML($svg_content);

    // remove unwanted comments
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//comment()') as $comment) {
        $comment->parentNode->removeChild($comment);
    }

    // add class to svg
    if (!empty($svgClass)) {
        foreach ($dom->getElementsByTagName('svg') as $element) {
            $element->setAttribute('class', $svgClass);
        }
    }

    // remove unwanted tags
    $title = $dom->getElementsByTagName('title');
    if ($title['length']) {
        $dom->documentElement->removeChild($title[0]);
    }
    $desc = $dom->getElementsByTagName('desc');
    if ($desc['length']) {
        $dom->documentElement->removeChild($desc[0]);
    }
    $defs = $dom->getElementsByTagName('defs');
    if ($defs['length']) {
        $dom->documentElement->removeChild($defs[0]);
    }

    // remove unwanted id attribute in g tag
    $g = $dom->getElementsByTagName('g');
    foreach ($g as $el) {
        $el->removeAttribute('id');
    }
    $mask = $dom->getElementsByTagName('mask');
    foreach ($mask as $el) {
        $el->removeAttribute('id');
    }
    $rect = $dom->getElementsByTagName('rect');
    foreach ($rect as $el) {
        $el->removeAttribute('id');
    }
    $xpath = $dom->getElementsByTagName('path');
    foreach ($xpath as $el) {
        $el->removeAttribute('id');
    }
    $circle = $dom->getElementsByTagName('circle');
    foreach ($circle as $el) {
        $el->removeAttribute('id');
    }
    $use = $dom->getElementsByTagName('use');
    foreach ($use as $el) {
        $el->removeAttribute('id');
    }
    $polygon = $dom->getElementsByTagName('polygon');
    foreach ($polygon as $el) {
        $el->removeAttribute('id');
    }
    $ellipse = $dom->getElementsByTagName('ellipse');
    foreach ($ellipse as $el) {
        $el->removeAttribute('id');
    }

    $string = $dom->saveXML($dom->documentElement);

    // remove empty lines
    $string = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);

    $cls = array('svg-icon');

    if (!empty($class)) {
        $cls = array_merge($cls, explode(' ', $class));
    }

    $asd = explode('/media/', $path);
    if (isset($asd[1])) {
        $path = 'assets/media/'.$asd[1];
    }

    $output = "<!--begin::Svg Icon | path: $path-->\n";
    $output .= '<span class="'.implode(' ', $cls).'">'.$string.'</span>';
    $output .= "\n<!--end::Svg Icon-->";

    return $output;
}