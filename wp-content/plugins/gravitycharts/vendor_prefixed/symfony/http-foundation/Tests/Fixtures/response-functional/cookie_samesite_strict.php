<?php
/**
 * @license MIT
 *
 * Modified by gravitykit on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

use GravityKit\GravityCharts\Symfony\Component\HttpFoundation\Cookie;

$r = require __DIR__.'/common.inc';

$r->headers->setCookie(new Cookie('CookieSamesiteStrictTest', 'StrictValue', 0, '/', null, false, true, false, Cookie::SAMESITE_STRICT));
$r->sendHeaders();
