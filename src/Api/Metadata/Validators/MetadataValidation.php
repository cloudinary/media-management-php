<?php
/**
 * This file is part of the Cloudinary Media Management PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\MediaManagement\Api\Metadata\Validators;

use Cloudinary\MediaManagement\Api\Metadata\Metadata;

/**
 * Represents the base class for metadata fields validation mechanisms.
 *
 * @api
 */
abstract class MetadataValidation extends Metadata
{
    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var int $min
     */
    protected $min;

    /**
     * @var int $max
     */
    protected $max;

    /**
     * @var bool $equals
     */
    protected $equals;

    /**
     * @var mixed $value
     */
    protected $value;

    /**
     * Gets the keys for all the properties of this object.
     *
     * @return string[]
     */
    public function getPropertyKeys(): array
    {
        return ['type', 'min', 'max', 'equals', 'value'];
    }
}
