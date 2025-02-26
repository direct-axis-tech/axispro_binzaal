<?php

use App\Models\Accounting\Dimension;
use Axispro\Admin\HeaderOrFooter;

/**
 * Returns the path to the binary of pdf/image for snappy
 *
 * @param string $type
 * @return string
 */
function snappy_binary($type)
{
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    return $isWindows
        ? base_path("vendor/wemersonjanuario/wkhtmltopdf-windows/bin/64bit/wkhtmlto{$type}.exe")
        : base_path("vendor/h4cc/wkhtmlto{$type}-amd64/bin/wkhtmlto{$type}-amd64");
}

/**
 * Get the illustration path for the application
 *
 * @param string $path
 * @param boolean $themed
 * @param boolean|null $secure
 * @return string
 */
function illustration($path, $themed = true, $secure = null)
{
    $path = join_paths('illustrations/sketchy-1', $path);

    return image($path, $themed, $secure);
}

/**
 * Get the image path for the application
 *
 * @param string $path
 * @param boolean $themed
 * @param boolean|null $secure
 * @return string
 */
function image($path, $themed = true, $secure = null)
{
    $publicPath = media_path($path);

    if ($themed && in_dark_mode()) {
        $_publicPath = strtr($publicPath, [
            '-dark.jpg' => '.svg',
            '-dark.svg' => '.png',
            '-dark.png' => '.jpg',
            '.svg' => '-dark.svg',
            '.png' => '-dark.png',
            '.jpg' => '-dark.jpg'
        ]);

        if (file_exists($_publicPath)) {
            $publicPath = $_publicPath;
        }
    }

    return asset(Str::after($publicPath, public_path()), $secure);
}

/**
 * Generate the media path for the application.
 *
 * @param  string  $path
 * @param  bool|null  $secure
 * @return string
 */
function media($path, $secure = null)
{
    return asset(join_paths('media', $path), $secure);
}

/**
 * Generate a media path for the application.
 *
 * @param  string  $path
 * @return string
 */
function media_path($path)
{
    return join_paths(public_path(), 'media', $path);
}

/**
 * Join multiple paths
 * 
 * @param string $paths,...
 * @return string
 */
function join_paths(...$paths)
{
    $hasLeadingSlash = substr(reset($paths), 0, 1) === '/';
    $hasTrailingSlash = substr(end($paths), -1) === '/';
    
    $paths = array_map(
        function($path) { return trim($path, "\\/"); },
        $paths
    );
    
    $path = implode("/", $paths);
    $hasLeadingSlash && ($path = '/'.ltrim($path, '/'));
    $hasTrailingSlash && ($path = rtrim($path, '/') . '/');

    return $path;
}

/**
 * Get PDF Header path
 *
 * @param int $dimensionId
 * @param int $transType
 * @return string
 */
function pdf_header_path($dimensionId = null, $transType = null)
{
    if ($dimensionId && ($file = HeaderOrFooter::existingFile('dimension_header', $dimensionId))) {
        return HeaderOrFooter::path($file);
    }

    if ($file = HeaderOrFooter::existingFile('company_header')) {
        return HeaderOrFooter::path($file);
    }

    return dirname(base_path()).'/company/0/images/pdf-header-top.jpg';
}

/**
 * Get PDF Footer path
 *
 * @param int $dimensionId
 * @param int $transType
 * @return string
 */
function pdf_footer_path($dimensionId = null, $transType = null)
{
    if ($dimensionId && ($file = HeaderOrFooter::existingFile('dimension_footer', $dimensionId))) {
        return HeaderOrFooter::path($file);
    }

    if ($file = HeaderOrFooter::existingFile('company_footer')) {
        return HeaderOrFooter::path($file);
    }

    return dirname(base_path()).'/company/0/images/pdf-footer-image.jpg';
}