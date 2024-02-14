<?php

namespace owonico\tag;

class Tag{

    /** @var string */

    public $name;

    /** @var string */

    public $displayFormat;

    /** @var array */


    

    public function __construct(string $name, string $displayFormat) {

        $this->name = $name;

        $this->displayFormat = $displayFormat;

    }

   public function getName(): string {
     return $this->name;
   }
    
    public function getDisplayFormat(): string {
        return $this->displayFormat;
    }
   }
    
    
