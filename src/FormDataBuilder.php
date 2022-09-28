<?php

namespace ThinLayer\Php2Curl;

class FormDataBuilder
{
    private array $post = [];
    private string $output = '';

    public function post(array $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function build(): string
    {
        if (!$this->post) {
            return $this->output;
        }

        $this->buildArray($this->post);
        return $this->output;
    }

    private function buildArray(array $value, string $prefix = ''): void
    {
        foreach ($value as $key1 => $value1) {
            $key1 = $prefix ? "{$prefix}[$key1]" : $key1;
            if (is_array($value1)) {
                $this->buildArray($value1, $key1);
            } else {
                $value1 = $this->escape($value1);
                $this->output .= " --form '{$key1}={$value1}'";
            }
        }
    }

    private function escape($parameter): string
    {
        return addcslashes($parameter, "'");
    }
}
