<?php
namespace Omeka\Api;

// Stub for a generic API Response
class Response
{
    private $content;

    public function __construct($content = null)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
