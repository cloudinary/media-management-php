<?php

namespace Cloudinary\MediaManagement\Test;

$cloudinary = new \Cloudinary\Cloudinary();

      $cloudinary->uploadApi()->upload("tests/assets/sample.png",
          [ "quality_analysis" => TRUE, "tags" => ['animal', 'dog'], "eager" => Delivery::format(Format::auto())]);
