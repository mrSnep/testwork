<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    //
    protected $table = 'files';

    protected $fillable = ['title', 'description', 'user_id', 'type', 'filename','size','downloaded'];

    /**
     * Returns a formatted post content entry,
     * this ensures that line breaks are returned.
     *
     * @return string
     */
    public function fulldescription()
    {
        return nl2br($this->description);
    }

    /**
     * Returns a formatted post content entry,
     * this ensures that line breaks are returned.
     *
     * @return string
     */
    public function smalldescription()
    {
        return self::desctrunk(strip_tags($this->description),17);
    }

    public function getType()
    {
        $types = array('<i class="glyphicon glyphicon-font"></i>','<i class="glyphicon glyphicon-picture"></i>','<i class="glyphicon glyphicon-compressed"></i>');
        return $types[$this->type];

    }

    private static function desctrunk($string, $your_desired_width) {
        $text = substr($string, 0, $your_desired_width);
        $text = rtrim($text, "!,.-");
        if(strlen($text)>1) $text .="...";

        return $text;
    }


}
