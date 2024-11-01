<?php

/**
  Copyright (C) <2015>  Myjive Inc. <info@myjive.com>

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

//register Facebook autoloader
require __DIR__ . '/vendor/Facebook/autoload.php';

//register Google autloader
require __DIR__ . '/vendor/Google/autoload.php';

//return some class map array for manual load
return array(
    'TwitterAPIExchange' => __DIR__ . '/vendor/Twitter/TwitterAPIExchange.php',
    'MetzWeb\Instagram\Instagram' => __DIR__ . '/vendor/Instagram/Instagram.php',
    'MetzWeb\Instagram\InstagramException' => __DIR__ . '/vendor/Instagram/InstagramException.php',
);