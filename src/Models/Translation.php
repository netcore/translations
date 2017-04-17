<?php

namespace Netcore\Translator\Models;

use Illuminate\Database\Eloquent\Model;
use Netcore\Translator\PassThroughs\Translation\Import;
use Netcore\Translator\PassThroughs\Translation\Export;

class Translation extends Model
{
    /**
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * @return Import
     */
    public function import()
    {
        return new Import($this);
    }

    /**
     * @return Export
     */
    public function export()
    {
        return new Export($this);
    }
}
