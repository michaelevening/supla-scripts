<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use suplascripts\models\BelongsToUser;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\User;
use Twig_Environment;
use Twig_Loader_Array;

class FeedbackInterpolator {
    use HasSuplaApi;

    const URL_FETCH_TIMEOUT = 5;
    const NOT_CONNECTED_RESPONSE = ' DISCONNECTED ';

    public function __construct($subject = null) {
        $this->twig = new Twig_Environment(new Twig_Loader_Array([]));
        $this->twig->addExtension(new FeedbackTwigExtension());
        if ($subject instanceof BelongsToUser) {
            $this->user = $subject->user;
        } elseif ($subject instanceof User) {
            $this->user = $subject;
        }
    }

    public function interpolate($feedback, $noCache = false) {
        return self::wrapInUserTimezone($this->user, function () use ($noCache, $feedback) {
            return $this->doInterpolate($feedback, $noCache);
        });
    }

    private function doInterpolate($feedback, $noCache = false) {
        if (!$feedback) {
            return $feedback;
        }
        $feedback = preg_replace_callback('#\[\[(http.+?)\]\]#i', function ($match) {
            return FeedbackTwigExtension::getUrlContents($match[1]);
        }, $feedback);
        $feedback = preg_replace_callback('#{{(\d+)\|(on|temperature|humidity|hi)\|(bool|number|compare):?([^}]+?)?}}#', function ($match) {
            $replacement = $this->replaceChannelState($match[1], $match[2], $match[3], isset($match[4]) ? explode(',', $match[4]) : []);
            return $replacement !== null ? $replacement : $match[0];
        }, $feedback);
        try {
            $template = $this->twig->createTemplate($feedback);
            $feedback = $template->render(['noCache' => $noCache]);
        } catch (\Throwable $e) {
            $feedback .= ' (ERROR: ' . $e->getMessage() . ')';
        }
        return trim($feedback);
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

    public static function wrapInUserTimezone($user, $function) {
        $defaultTimezone = date_default_timezone_get();
        if ($user) {
            date_default_timezone_set($user->timezone);
        }
        $result = $function();
        date_default_timezone_set($defaultTimezone);
        return $result;
    }
}
