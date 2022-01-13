<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;

/**
 * Pagination extracts pagination parameters from the specified request.
 *
 * This class does not perform data segmentation via pages, it simply extracts and validates parameters for it.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Pagination
{
    public $pageKeyword = 'page';

    public $cursorKeyword = 'cursor';

    public $perPageKeyword = 'per-page';

    public $perPageMin = 1;

    public $perPageMax = 50;

    public $perPageDefault = 15;

    public $perPage;

    public $page;

    public $cursor;

    public function __construct(array $config = [])
    {
        $this->pageKeyword = $config['page']['keyword'] ?? $this->pageKeyword;
        $this->cursorKeyword = $config['cursor']['keyword'] ?? $this->cursorKeyword;
        $this->perPageKeyword = $config['per_page']['keyword'] ?? $this->perPageKeyword;
        $this->perPageMin = $config['per_page']['min'] ?? $this->perPageMin;
        $this->perPageMax = $config['per_page']['max'] ?? $this->perPageMax;
        $this->perPageDefault = $config['per_page']['default'] ?? $this->perPageDefault;
    }

    /**
     * Fills up internal fields from given request data, performing validation during the process.
     *
     * @param array $params request parameters.
     * @return static self instance.
     */
    public function fill($params): self
    {
        if (isset($params[$this->perPageKeyword])) {
            $perPage = $params[$this->perPageKeyword];

            if (!is_int($perPage) && !ctype_digit($perPage)) {
                throw new InvalidQueryException('"' . $this->perPageKeyword . '" should be an integer.');
            }

            if ($perPage < $this->perPageMin || $perPage > $this->perPageMax) {
                throw new InvalidQueryException('"' . $this->perPageKeyword . '" should be in range: ' . $this->perPageMin . '..' . $this->perPageMax);
            }

            $this->perPage = $perPage;
        } else {
            $this->perPage = $this->perPageDefault;
        }

        if (isset($params[$this->pageKeyword])) {
            $page = $params[$this->pageKeyword];

            if (!is_int($page) && !ctype_digit($page)) {
                throw new InvalidQueryException('"' . $this->pageKeyword . '" should be an integer.');
            }

            if ((int) $page < 1) {
                throw new InvalidQueryException('"' . $this->pageKeyword . '" should be > 0.');
            }

            $this->page = $page;
        }

        if (isset($params[$this->cursorKeyword])) {
            $this->cursor = $params[$this->cursorKeyword];
        }

        return $this;
    }
}