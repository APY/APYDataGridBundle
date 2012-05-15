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

class DateTimeColumn extends TextColumn
{
    protected $locale;

    protected $charset;

    protected $datetype = \IntlDateFormatter::MEDIUM;

    protected $timetype = \IntlDateFormatter::MEDIUM;

    protected $format;

    protected $fallbackFormat = 'Y-m-d H:i:s';

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

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->format = $this->getParam('format');
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function isFiltered()
    {
        if (isset($this->data) && strtotime($this->data) !== false) {
            return true;
        }

        $this->data = null;

        return false;
    }

    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_EQ, new \DateTime($this->data)));
    }

    public function renderCell($value, $row, $router)
    {
        if ($value != null)
        {
            $timezone = new \DateTimeZone(date_default_timezone_get());

            $date = $this->getDatetime($value, $timezone);

            if (isset($this->format)) {
                $value = $date->format($this->format);
            } else {
                try {
                    $formatter = new \IntlDateFormatter(
                            $this->locale,
                            $this->datetype,
                            $this->timetype,
                            date_default_timezone_get(),
                            \IntlDateFormatter::GREGORIAN
                    );

                    // If intl extension is activated but not working
                    if ($formatter instanceof \IntlDateFormatter) {
                        $value = $formatter->format($date->getTimestamp());
                    } else {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    $value = $date->format($this->fallbackFormat);
                }
            }

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
     * DateTimeHelper::getDatetime() from SonataIntlBundle
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
        $date->setTimezone($timezone);

        return $date;
    }

    public function getParentType()
    {
        return 'text';
    }

    public function getType()
    {
        return 'datetime';
    }
}
