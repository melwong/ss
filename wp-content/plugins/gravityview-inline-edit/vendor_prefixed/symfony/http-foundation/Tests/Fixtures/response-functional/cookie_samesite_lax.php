<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 20-February-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

use GravityKit\GravityEdit\Symfony\Component\HttpFoundation\Cookie;

$r = require __DIR__.'/common.inc';

$r->headers->setCookie(new Cookie('CookieSamesiteLaxTest', 'LaxValue', 0, '/', null, false, true, false, Cookie::SAMESITE_LAX));
$r->sendHeaders();
