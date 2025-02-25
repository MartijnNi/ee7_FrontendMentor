<?php
namespace {{namespace}}\Core\Traits;

trait HasTags
{
    private $variables = [];
    public $tagdata = '';
    private $tagparams = [];

    /**
     * Retrieve the tag data from the template.
     *
     * @return string
     */
    protected function tagData(): string
    {
        if (isset(ee()->TMPL)) {
            $this->tagdata = ee()->TMPL->tagdata;
        }
        return $this->tagdata;
    }

    /**
     * Retrieve the parameters from the template.
     *
     * @return array
     */
    protected function tagParams(): array
    {
        $params = [];
        if (isset(ee()->TMPL)) {
            $params = ee()->TMPL->tagparams;
        }
        return $params;
    }
      /**
     * Retrieve the parameters from the template.
     *
     * @return array
     */
    protected function getTagParam($key): string
    {
        $param = false;
        if (isset(ee()->TMPL)) {
            $param = ee()->TMPL->tagparams[$key] ?? $param;
        }
        return $param;
    }
      /**
     * Retrieve the tag data from the template.
     *
     * @return string
     */
    protected function variables(): array
    {
        return $this->variables;
    }

    /**
     * Set a variable at a specific index and key.
     *
     * @param int $index
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setVariable(int $index, string $key, $value): self
    {
        if (!isset($this->variables[$index])) {
            $this->variables[$index] = [];
        }
        $this->variables[$index][$key] = $value;

        return $this;
    }

    /**
     * Set multiple variables at a specific index.
     *
     * @param int $index
     * @param array $values
     * @return $this
     */
    public function setVariables(int $index, array $values): self
    {
        if (!isset($this->variables[$index])) {
            $this->variables[$index] = [];
        }
        $this->variables[$index] = array_merge($this->variables[$index], $values);

        return $this;
    }

    /**
     * Get variables for a specific index.
     *
     * @param int $index
     * @return array
     */
    public function getVariablesAt(int $index): array
    {
        return $this->variables[$index] ?? [];
    }

    /**
     * Render the tag output by replacing variables in the tag data.
     *
     * @return string
     */
    public function parse(): string
    {
        return ee()->TMPL->parse_variables($this->tagdata, $this->variables);
    }

    /**
     * Get the current instance for method chaining.
     *
     * @return $this
     */
    public function tag(): self
    {
        return $this;
    }
}