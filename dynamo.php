<?php
/**
 * DynaMo
 *
 * @package           DynaMo
 * @author            WP SYNTEX
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       DynaMo
 * Description:       Improves the WordPress translations performance
 * Version:           1.1-dev
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            WP SYNTEX
 * Author URI:        https://polylang.pro
 * Text Domain:       dynamo
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Copyright 2021-2022 WP SYNTEX
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * This program incorporates work from the plugin WP Performance Pack
 * Copyright 2014-2020 Björn Ahrens (email : bjoern@ahrens.net)
 * WP Performance Pack is released under the GPL V2 or later.
 */

namespace WP_Syntex\DynaMo;

require __DIR__ . '/vendor/autoload.php';
( new Plugin() )->add_hooks();
