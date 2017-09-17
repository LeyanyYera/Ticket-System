<?php

namespace TicketBundle\Services;


class Twig extends \Twig_Extension {

    public function getFunctions(){
        return array(
            'generateTable' => new \Twig_Function_Method($this, 'generateTable'),
            'generateButtonBar' => new \Twig_Function_Method($this, 'generateButtonBar')
        );
    }

    public function generateButtonBar(){
        $button_bar = '<div class="form-control-static pull-right" style="padding:20px 15px 0 0">
                            <input type="submit" value="Accept" class="btn btn-primary">
                            <input type="button" id="cancel" value="Cancel" class="btn btn-primary">
                        </div>';
        return $button_bar;
    }

    public function getName(){
        return 'TicketBundle';
    }
}