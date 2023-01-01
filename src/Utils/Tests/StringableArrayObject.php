<?php
declare(strict_types = 1);
/**
 * /src/Utils/Tests/StringableArrayObject.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Utils\Tests;

use App\Utils\JSON;
use ArrayObject;
use JsonException;
use Stringable;

/**
 * Class StringableArrayObject
 *
 * @psalm-suppress MissingTemplateParam
 *
 * @package App\Utils\Tests
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
class StringableArrayObject extends ArrayObject implements Stringable
{
    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        $iterator = static fn (mixed $input): mixed => $input instanceof Stringable ? (string)$input : $input;

        return JSON::encode(array_map($iterator, $this->getArrayCopy()));
    }
}
