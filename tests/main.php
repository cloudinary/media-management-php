<?php

namespace Cloudinary\MediaManagement\Test;

$cldMediaManagement = new \Cloudinary\MediaManagement\CldMediaManagement();

      $cldMediaManagement->uploadApi()->upload("tests/assets/sample.png",
          [ "quality_analysis" => TRUE, "tags" => ['animal', 'dog'], "eager" => Delivery::format(Format::auto())]);
