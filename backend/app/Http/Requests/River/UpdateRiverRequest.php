<?php

namespace App\Http\Requests\River;

use App\Models\River;

class UpdateRiverRequest extends StoreRiverRequest
{
    public function authorize(): bool
    {
        $river = $this->route('river');

        return $river instanceof River
            && $this->user()?->can('update', $river) === true;
    }
}
