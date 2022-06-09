<?php

declare(strict_types=1);

namespace Yiisoft\Html\Tag\Base;

use InvalidArgumentException;
use Stringable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Html\Html;
use Yiisoft\Html\NoEncodeStringableInterface;

/**
 * HTML tag. Base class for all tags.
 */
abstract class Tag implements NoEncodeStringableInterface
{
    protected array $attributes = [];
    protected array $style = [];

    /**
     * @deprecated use addAttributes instead
     *
     * Add a set of attributes to existing tag attributes.
     * Same named attributes are replaced.
     *
     * @param array $attributes Name-value set of attributes.
     *
     * @return static
     */
    final public function attributes(array $attributes): self
    {
        return $this->addAttributes($attributes, false);
    }

    /**
     * @deprecated use addAttributes instead
     *
     * Replace attributes with a new set.
     *
     * @param array $attributes Name-value set of attributes.
     *
     * @return static
     */
    final public function replaceAttributes(array $attributes): self
    {
        return $this->addAttributes($attributes);
    }

    /**
     * Add or replace attributes with a new set
     *
     * @param array $attributes
     * @param bool $replace
     *
     * @return self
     */
    final public function addAttributes(array $attributes, bool $replace = true): self
    {
        $new = clone $this;

        if ($replace) {
            $new->attributes = $attributes;
        } else {
            $new->attributes = array_merge($new->attributes, $attributes);
        }

        $style = ArrayHelper::remove($new->attributes, 'style');

        return $style !== null ? $new->addStyle($style) : $new;
    }

    /**
     * Add or replace/remove style attribute with a new set/string
     *
     * @param mixed $style
     * @param bool $replace
     *
     * @return self
     */
    final public function addStyle($style, bool $replace = true): self
    {
        $isString = is_string($style) || $style instanceof Stringable;

        if ($isString) {
            $style = Html::cssStyleToArray($style);
        } elseif ($style !== null && !is_array($style)) {
            $type = is_object($style) ? get_class($style) : gettype($style);

            throw new InvalidArgumentException('$style must be null, string, array or Stringable instance. ' . $type . ' given.');
        }

        $new = clone $this;

        if ($replace || $style === null) {
            $new->style = $style ?? [];
        } else {
            $new->style = array_merge($new->style, $style);
        }

        return $new;
    }

    /**
     * Set or remove current style param
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    final public function style(string $name, $value): self
    {
        $new = clone $this;
        $new->style[$name] = $value;

        return $new;
    }

    /**
     * Union attributes with a new set.
     *
     * @param array $attributes Name-value set of attributes.
     *
     * @return static
     */
    final public function unionAttributes(array $attributes): self
    {
        $new = clone $this;
        $new->attributes += $attributes;
        return $new;
    }

    /**
     * Set attribute value.
     *
     * @param string $name Name of the attribute.
     * @param mixed $value Value of the attribute.
     *
     * @return static
     */
    final public function attribute(string $name, $value): self
    {
        if ($name === 'style') {
            return $this->addStyle($value);
        }

        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    /**
     * Set tag ID.
     *
     * @param string|null $id Tag ID.
     *
     * @return static
     */
    final public function id(?string $id): self
    {
        $new = clone $this;
        $new->attributes['id'] = $id;
        return $new;
    }

    final public function addClass(?string ...$class): self
    {
        $new = clone $this;
        Html::addCssClass(
            $new->attributes,
            array_filter($class, static fn ($c) => $c !== null)
        );

        return $new;
    }

    /**
     * @deprecated use addClass instead
     *
     * Add one or more CSS classes to the tag.
     *
     * @param string|null ...$class One or many CSS classes.
     *
     * @return static
     */
    final public function class(?string ...$class): self
    {
        return $this->addClass(...$class);
    }

    /**
     * @deprecated
     *
     * Replace current tag CSS classes with a new set of classes.
     *
     * @param string|null ...$class One or many CSS classes.
     *
     * @return static
     */
    final public function replaceClass(?string ...$class): self
    {
        $new = clone $this;
        $new->attributes['class'] = array_filter($class, static fn ($c) => $c !== null);
        return $new;
    }

    /**
     * Render the current tag attributes.
     *
     * @see Html::renderTagAttributes()
     */
    final protected function renderAttributes(): string
    {
        $this->prepareAttributes();
        return Html::renderTagAttributes($this->attributes);
    }

    protected function prepareAttributes(): void
    {
        if ($this->style) {
            $this->attributes['style'] = $this->style;
        }
    }

    final public function render(): string
    {
        return $this->before() . $this->renderTag() . $this->after();
    }

    protected function before(): string
    {
        return '';
    }

    protected function after(): string
    {
        return '';
    }

    /**
     * Render tag object into its string representation.
     *
     * @return string String representation of a tag object.
     */
    abstract protected function renderTag(): string;

    /**
     * Get tag name.
     *
     * @return string Tag name.
     */
    abstract protected function getName(): string;

    final public function __toString(): string
    {
        return $this->render();
    }
}
