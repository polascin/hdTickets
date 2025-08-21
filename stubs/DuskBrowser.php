<?php

namespace Laravel\Dusk;

class Browser
{
    public function visit($url) { return $this; }
    public function resize($width, $height) { return $this; }
    public function disableJavaScript() { return $this; }
    public function enableJavaScript() { return $this; }
    public function maximize() { return $this; }
    public function click($selector) { return $this; }
    public function type($field, $value) { return $this; }
    public function select($field, $value) { return $this; }
    public function check($field) { return $this; }
    public function uncheck($field) { return $this; }
    public function assertSee($text) { return $this; }
    public function assertDontSee($text) { return $this; }
    public function assertPathIs($path) { return $this; }
    public function waitFor($selector, $seconds = 5) { return $this; }
    public function screenshot($name) { return $this; }
}
