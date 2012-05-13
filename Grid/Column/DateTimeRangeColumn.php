<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;
use Symfony\Component\HttpFoundation\Request;

class DateTimeRangeColumn extends RangeColumn
{
    protected $locale;

    protected $charset;

    protected $datetype = \IntlDateFormatter::MEDIUM;

    protected $timetype = \IntlDateFormatter::MEDIUM;

    /**
     * Default Column constructor
     *
     * @param array $params
     * @param Request $request
     * @param string $charset
     * @return \Sorien\DataGridBundle\Grid\Column\Column
     */
    public function __construct($params = null, Request $request = null, $charset = 'UTF-8')
    {
        $this->locale = $request->getLocale(); // Symfony 2.0 only
        $this->charset = $charset;

        $this->__initialize((array) $params);
    }

    public function getFilters()
    {
        $result = array();

        if ($this->data['from'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_GTE, new \DateTime($this->data['from']));
        }

        if ($this->data['to'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_LTE, new \DateTime($this->data['to']));
        }

        return $result;
    }

    public function renderCell($value, $row, $router)
    {
        if ($value != null)
        {
            $timezone = new \DateTimeZone(date_default_timezone_get());

            $date = $this->getDatetime($value, $timezone);

            $formatter = new \IntlDateFormatter(
                    $this->locale,
                    $this->datetype,
                    $this->timetype,
                    date_default_timezone_get(),
                    \IntlDateFormatter::GREGORIAN
            );

            $value = $formatter->format($date->getTimestamp());

            //Fixes the charset by converting a string from an UTF-8 charset to the charset of the kernel.
            if ('UTF-8' !== $this->charset) {
                $value = mb_convert_encoding($value, $this->charset, 'UTF-8');
            }

            return parent::renderCell($value, $row, $router);
        }
        else
        {
            return '';
        }
    }

    /**
     * DateTimeHelper::getDatetime() from Sonata/IntlBundle
     *
     * @param \Datetime|string|integer $data
     * @param null|string timezone
     * @return \Datetime
     */
    protected function getDatetime($data, $timezone = null)
    {
        if($data instanceof \DateTime) {
            return $data;
        }

        // the format method accept array or integer
        if (is_numeric($data)) {
            $data = (int)$data;
        }

        if (is_string($data)) {
            $data = strtotime($data);
        }

        $date = new \DateTime();
        $date->setTimestamp($data);
        $date->setTimezone($timezone ?: $this->defaultTimezone);

        return $date;
    }

    public function getParentType()
    {
        return 'range';
    }

    public function getType()
    {
        return 'datetimerange';
    }
}
