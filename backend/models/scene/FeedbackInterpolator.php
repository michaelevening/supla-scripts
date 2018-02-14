<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;

class FeedbackInterpolator {
    use HasSuplaApi;

    const NOT_CONNECTED_RESPONSE = ' DISCONNECTED ';

    public function interpolate($feedback) {
        if (!$feedback) {
            return $feedback;
        }
        return preg_replace_callback('#{{(\d+)\|(on|temperature|humidity|hi)\|(bool|number|compare):?([^}]+?)?}}#', function ($match) {
            $replacement = $this->replaceChannelState($match[1], $match[2], $match[3], isset($match[4]) ? explode(',', $match[4]) : []);
            return $replacement !== null ? $replacement : $match[0];
        }, $feedback);
    }

    public function replaceChannelState($channelId, $field, $varType, $config) {
        $state = $this->getApi()->getChannelState($channelId);
        $desiredValue = $state->{$field};
        if (!$state->connected) {
            return self::NOT_CONNECTED_RESPONSE;
        }
        switch ($varType) {
            case 'bool':
                return $desiredValue ? ($config[0] ?? '1') : ($config[1] ?? '0');
            case 'number':
                return number_format(floatval($desiredValue), intval($config[0] ?? 1));
            case 'compare':
                $operator = $config[0] ?? '==';
                Assertion::inArray($operator, ['<', '<=', '>', '>=', '==']);
                $compareTo = $config[1] ?? 0;
                if (preg_match('@(\d+)#(temperature|humidity)@', $compareTo, $matches)) {
                    $compateToInterpolation = '{{' . "$matches[1]|$matches[2]|number}}";
                    $compareTo = $this->interpolate($compateToInterpolation);
                }
                eval('$result = ($desiredValue ' . $operator . ' $compareTo);');
                return $result ? ($config[2] ?? '1') : ($config[3] ?? '0');
        }
    }
}
