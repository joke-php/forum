<?php

declare(strict_types=1);

use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Templator\TemplatedResponse;
use Vasoft\Joke\Templator\TemplateEngine;

/**
 * @var TemplatedResponse $response
 * @var ServiceContainer  $container
 * @var TemplateEngine    $engine
 * @var string            $templatePath
 */
$response->builder->css->addToHead($templatePath . 'assets/styles.css');
$response->builder->js->addToBody($templatePath . 'assets/theme-switch.js');
$response->builder->bottomString->add(<<<'HTML'
<svg style="display:none">
  <symbol id="icon-edit" viewBox="0 0 24 24">
    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
  </symbol>
  <symbol id="icon-move" viewBox="0 0 24 24">
    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
    <path d="M12 11l3 3-3 3"/>
    <path d="M15 14H6"/>
  </symbol>
  <symbol id="icon-delete" viewBox="0 0 24 24">
    <path d="M3 6h18"/>
    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
    <line x1="10" y1="11" x2="10" y2="17"/>
    <line x1="14" y1="11" x2="14" y2="17"/>
  </symbol>
<symbol id="icon-pin" viewBox="0 0 24 24">
  <path d="M15.5 2.5l6 6-2.5 2.5-1.5-1.5-4 4 .5 3-1.5 1.5-6-6L8 10.5l3 .5 4-4-1.5-1.5L16 3l-0.5-0.5z"/>
  <path d="M8 16l-5 5"/>
</symbol>
</svg>
HTML);
