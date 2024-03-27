<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Routing\RouterInterface;

class NumberColumn extends Column
{
    protected static array $styles = [
        'decimal' => \NumberFormatter::DECIMAL,
        'percent' => \NumberFormatter::PERCENT,
        'money' => \NumberFormatter::CURRENCY,
        'currency' => \NumberFormatter::CURRENCY,
        'duration' => \NumberFormatter::DURATION,
        'scientific' => \NumberFormatter::SCIENTIFIC,
        'spellout' => \NumberFormatter::SPELLOUT,
    ];

    protected ?int $style = null;

    protected ?string $locale = null;

    protected ?int $precision = null;

    protected ?bool $grouping = null;

    protected ?int $roundingMode = null;

    protected ?string $ruleSet = null;

    protected ?string $currencyCode = null;

    protected ?bool $fractional = null;

    protected ?int $maxFractionDigits = null;

    public function __initialize(array $params): void
    {
        parent::__initialize($params);

        $this->setAlign($this->getParam('align', Column::ALIGN_RIGHT));
        $this->setStyle($this->getParam('style', 'decimal'));
        $this->setLocale($this->getParam('locale', \Locale::getDefault()));
        $this->setPrecision($this->getParam('precision', null));
        $this->setGrouping($this->getParam('grouping', false));
        $this->setRoundingMode($this->getParam('roundingMode', \NumberFormatter::ROUND_HALFUP));
        $this->setRuleSet($this->getParam('ruleSet'));
        $this->setCurrencyCode($this->getParam('currencyCode'));
        $this->setFractional($this->getParam('fractional', false));
        $this->setMaxFractionDigits($this->getParam('maxFractionDigits', null));
        if (\NumberFormatter::DURATION === $this->style) {
            $this->setLocale('en');
            $this->setRuleSet($this->getParam('ruleSet', '%in-numerals')); // or '%with-words'
        }

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
    }

    public function isQueryValid($query): bool
    {
        $result = \array_filter((array) $query, 'is_numeric');

        return !empty($result);
    }

    public function renderCell(mixed $value, Row $row, RouterInterface $router): mixed
    {
        if (\is_callable($this->callback)) {
            return \call_user_func($this->callback, $value, $row, $router);
        }

        return $this->getDisplayedValue($value);
    }

    public function getDisplayedValue($value)
    {
        if (null !== $value && '' !== $value) {
            $formatter = new \NumberFormatter($this->locale, $this->style);

            if (null !== $this->precision) {
                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->precision);
                $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
            }

            if (null !== $this->ruleSet) {
                $formatter->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, $this->ruleSet);
            }

            if (null !== $this->maxFractionDigits) {
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $this->maxFractionDigits);
            }

            $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

            if (\NumberFormatter::PERCENT === $this->style && !$this->fractional) {
                $value /= 100;
            }

            if (\NumberFormatter::CURRENCY === $this->style) {
                if (null === $this->currencyCode) {
                    $this->currencyCode = $formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
                }

                if (3 !== \strlen($this->currencyCode)) {
                    throw new TransformationFailedException('Your locale definition is not complete, you have to define a language and a country. (.e.g en_US, fr_FR)');
                }

                $value = $formatter->formatCurrency($value, $this->currencyCode);
            } else {
                $value = $formatter->format($value);
            }

            if (\intl_is_failure($formatter->getErrorCode())) {
                throw new TransformationFailedException($formatter->getErrorMessage());
            }

            if (\array_key_exists((string) $value, $this->values)) {
                $value = $this->values[$value];
            }

            return $value;
        }

        return '';
    }

    public function getFilters(Source|string $source): array
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            // Transforme in number for ODM
            $filters[] = (null === $filter->getValue()) ? $filter : $filter->setValue($filter->getValue() + 0);
        }

        return $filters;
    }

    public function setStyle(string $style): static
    {
        if (!isset(static::$styles[$style])) {
            throw new \InvalidArgumentException(\sprintf('Expected parameter of style "%s", "%s" given', \implode('", "', \array_keys(static::$styles)), $this->style));
        }

        $this->style = static::$styles[$style];

        return $this;
    }

    public function getStyle(): ?int
    {
        return $this->style;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setPrecision(?int $precision): static
    {
        $this->precision = $precision;

        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setGrouping(bool $grouping): static
    {
        $this->grouping = $grouping;

        return $this;
    }

    public function getGrouping(): ?bool
    {
        return $this->grouping;
    }

    public function setRoundingMode(int $roundingMode): static
    {
        $this->roundingMode = $roundingMode;

        return $this;
    }

    public function getRoundingMode(): ?int
    {
        return $this->roundingMode;
    }

    public function setRuleSet(?string $ruleSet): static
    {
        $this->ruleSet = $ruleSet;

        return $this;
    }

    public function getRuleSet(): ?string
    {
        return $this->ruleSet;
    }

    public function setCurrencyCode(?string $currencyCode): static
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setFractional(?bool $fractional): static
    {
        $this->fractional = $fractional;

        return $this;
    }

    public function getFractional(): ?bool
    {
        return $this->fractional;
    }

    public function setMaxFractionDigits(?int $maxFractionDigits): static
    {
        $this->maxFractionDigits = $maxFractionDigits;

        return $this;
    }

    public function getMaxFractionDigits(): ?int
    {
        return $this->maxFractionDigits;
    }

    public function getType(): string
    {
        return 'number';
    }
}
