<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Routing\RouterInterface;

class DateTimeColumn extends Column
{
    protected int $dateFormat = \IntlDateFormatter::MEDIUM;

    protected int $timeFormat = \IntlDateFormatter::MEDIUM;

    protected ?string $format = null;

    protected string $fallbackFormat = 'Y-m-d H:i:s';

    protected ?string $timezone = null;

    public function __initialize(array $params): void
    {
        parent::__initialize($params);

        $this->setFormat($this->getParam('format'));
        $this->setOperators($this->getParam('operators', [
            self::OPERATOR_EQ,
            self::OPERATOR_NEQ,
            self::OPERATOR_LT,
            self::OPERATOR_LTE,
            self::OPERATOR_GT,
            self::OPERATOR_GTE,
            self::OPERATOR_BTW,
            self::OPERATOR_BTWE,
            self::OPERATOR_ISNULL,
            self::OPERATOR_ISNOTNULL,
        ]));
        $this->setDefaultOperator($this->getParam('defaultOperator', self::OPERATOR_EQ));
        $this->setTimezone($this->getParam('timezone', \date_default_timezone_get()));
    }

    public function isQueryValid($query): bool
    {
        $result = \array_filter((array) $query, [$this, 'isDateTime']);

        return !empty($result);
    }

    protected function isDateTime($query): bool
    {
        return false !== \strtotime($query);
    }

    public function getFilters(Source|string $source): array
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            $filters[] = (null === $filter->getValue()) ? $filter : $filter->setValue(new \DateTime($filter->getValue()));
        }

        return $filters;
    }

    public function renderCell(mixed $value, Row $row, RouterInterface $router): mixed
    {
        $value = $this->getDisplayedValue($value);

        if (\is_callable($this->callback)) {
            $value = \call_user_func($this->callback, $value, $row, $router);
        }

        return $value;
    }

    public function getDisplayedValue($value): string
    {
        if (!empty($value)) {
            $dateTime = $this->getDatetime($value, new \DateTimeZone($this->getTimezone()));

            if (isset($this->format)) {
                $value = $dateTime->format($this->format);
            } else {
                try {
                    $transformer = new DateTimeToLocalizedStringTransformer(null, $this->getTimezone(), $this->dateFormat, $this->timeFormat);
                    $value = $transformer->transform($dateTime);
                } catch (\Exception) {
                    $value = $dateTime->format($this->fallbackFormat);
                }
            }

            if (\array_key_exists((string) $value, $this->values)) {
                $value = $this->values[$value];
            }

            return $value;
        }

        return '';
    }

    /**
     * DateTimeHelper::getDatetime() from SonataIntlBundle.
     */
    protected function getDatetime(\DateTime|\DateTimeImmutable|\MongoDate|\MongoTimestamp|string|int $data, \DateTimeZone $timezone): \DateTimeInterface
    {
        if ($data instanceof \DateTime || $data instanceof \DateTimeImmutable) {
            return $data->setTimezone($timezone);
        }

        // the format method accept array or integer
        if (\is_numeric($data)) {
            $data = (int) $data;
        }

        if (\is_string($data)) {
            $data = \strtotime($data);
        }

        // MongoDB Date and Timestamp
        if ($data instanceof \MongoDate || $data instanceof \MongoTimestamp) {
            $data = $data->sec;
        }

        // Mongodb bug ? timestamp value is on the key 'i' instead of the key 't'
        if (\is_array($data) && \array_keys($data) === ['t', 'i']) {
            $data = $data['i'];
        }

        $date = new \DateTime();
        $date->setTimestamp($data);
        $date->setTimezone($timezone);

        return $date;
    }

    public function setFormat(?string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setTimezone($timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getType(): string
    {
        return 'datetime';
    }
}
