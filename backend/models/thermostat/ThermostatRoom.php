<?php

namespace suplascripts\models;

use Assert\Assert;
use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\supla\SuplaApi;

/**
 * @property int $id
 * @property string $name
 * @property string $thermometers
 * @property string $heaters
 * @property string $coolers
 */
class ThermostatRoom extends Model
{
    const TABLE_NAME = 'thermostat_rooms';
    const NAME = 'name';
    const THERMOMETERS = 'thermometers';
    const HEATERS = 'heaters';
    const COOLERS = 'coolers';
    const USER_ID = 'userId';

    protected $fillable = [self::NAME, self::THERMOMETERS, self::HEATERS, self::COOLERS];
    protected $jsonEncoded = [self::THERMOMETERS, self::HEATERS, self::COOLERS];

    public static function create(array $attributes = [])
    {
        $room = new self($attributes);
        $room->save();
        return $room;
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function validate(array $attributes = null): array
    {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::NAME);
        // TODO validate channels
    }
}
