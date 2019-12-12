<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * InvalidQueryException indicates error during request processing.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class InvalidQueryException extends HttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, ?int $code = 0, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct($statusCode, $message, $previous, [], $code);
    }
}
