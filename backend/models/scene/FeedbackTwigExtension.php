<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace suplascripts\models\scene;

use suplascripts\models\HasSuplaApi;

class FeedbackTwigExtension extends \Twig_Extension {
    use HasSuplaApi;

    public function getFunctions() {
        return [
            new \Twig_Function('state', [$this, 'getChannelState']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('colorName', [$this, 'getNearestColorName']),
            new \Twig_Filter('colorNamePl', [$this, 'getNearestColorNamePolish']),
        ];
    }

    public function getChannelState($channelId) {
        return $this->getApi()->getChannelState($channelId);
    }

    /**
     * @see https://stackoverflow.com/a/2994015/878514
     */
    public function getNearestColorName($color): string {
        $colors = [
            "black" => [0, 0, 0],
            "green" => [0, 128, 0],
            "silver" => [192, 192, 192],
            "lime" => [0, 255, 0],
            "gray" => [128, 0, 128],
            "olive" => [128, 128, 0],
            "white" => [255, 255, 255],
            "yellow" => [255, 255, 0],
            "maroon" => [128, 0, 0],
            "navy" => [0, 0, 128],
            "red" => [255, 0, 0],
            "orange" => [255, 165, 0],
            "blue" => [0, 0, 255],
            "purple" => [128, 0, 128],
            "teal" => [0, 128, 128],
            "fuchsia" => [255, 0, 255],
            "aqua" => [0, 255, 255],
        ];
        if (is_object($color) && property_exists($color, 'color')) {
            $color = $color->color;
        }
        $value = str_replace('0x', '#', $color);
        $html2rgb = function ($color) {
            if ($color[0] == '#')
                $color = substr($color, 1);

            if (strlen($color) == 6)
                list($r, $g, $b) = array($color[0] . $color[1],
                    $color[2] . $color[3],
                    $color[4] . $color[5]);
            elseif (strlen($color) == 3)
                list($r, $g, $b) = array($color[0] . $color[0],
                    $color[1] . $color[1], $color[2] . $color[2]);
            else
                return false;

            $r = hexdec($r);
            $g = hexdec($g);
            $b = hexdec($b);

            return array($r, $g, $b);
        };
        $distancel2 = function (array $color1, array $color2) {
            return sqrt(pow($color1[0] - $color2[0], 2) + pow($color1[1] - $color2[1], 2) + pow($color1[2] - $color2[2], 2));
        };
        $distances = array();
        $val = $html2rgb($value);
        foreach ($colors as $name => $c) {
            $distances[$name] = $distancel2($c, $val);
        }
        $mincolor = "";
        $minval = pow(2, 30); /*big value*/
        foreach ($distances as $k => $v) {
            if ($v < $minval) {
                $minval = $v;
                $mincolor = $k;
            }
        }
        return $mincolor;
    }

    public function getNearestColorNamePolish($color) {
        return [
            "black" => 'czarny',
            "green" => 'zielony',
            "silver" => 'jasny szary',
            "lime" => 'limonkowy',
            "gray" => 'szary',
            "olive" => 'oliwkowy',
            "white" => 'biały',
            "yellow" => 'żółty',
            "maroon" => 'kasztanowaty',
            "navy" => 'granatowy',
            "red" => 'czerwony',
            "orange" => 'pomarańczowy',
            "blue" => 'niebieski',
            "purple" => 'fioletowy',
            "teal" => 'turkusowy',
            "fuchsia" => 'różowy',
            "aqua" => 'morski',
        ][$this->getNearestColorName($color)];
    }
}