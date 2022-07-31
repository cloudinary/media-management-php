<?php

require_once 'CloudinaryFilter.php';

use Sami\Parser\Filter\CloudinaryFilter;
use Sami\Sami;

$docsDir = __DIR__ . '/';
$srcDir  = $docsDir . '../src/';

return new Sami(
    $srcDir,
    [
        'theme'                => 'cloudinary',
        'template_dirs'        => [$docsDir . 'themes'],
        'title'                => 'Cloudinary Media Management PHP SDK',
        'version'              => '0.1.1-beta',
        'build_dir'            => $docsDir . 'build',
        'cache_dir'            => $docsDir . 'cache',
        'default_opened_level' => 1,
        'filter'               => new CloudinaryFilter(),
    ]
);
