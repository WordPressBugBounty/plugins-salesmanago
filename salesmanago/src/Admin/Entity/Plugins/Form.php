<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;

class Form extends AbstractEntity
{
    protected $owner         = '';
    protected $tags          = '';
    protected $tagsToRemove  = '';

    /**
     * Form constructor.
     * @param string $owner
     * @param string $tags
     * @param string $tagsToRemove
     */
    public function __construct($owner = '', $tags = '', $tagsToRemove = '')
    {
        $this->owner        = $owner;
        $this->tags         = $tags;
        $this->tagsToRemove = $tagsToRemove;
    }


}