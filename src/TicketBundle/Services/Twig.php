<?php

namespace TicketBundle\Services;

use Symfony\Component\Routing\RouterInterface;

class Twig extends \Twig_Extension {

    private $router;

    public function __construct(RouterInterface $router){
        $this->router = $router;
    }

    public function getFunctions(){
        return array(
//            new \Twig_Function_Method($this, 'generateButtonBar')
            'generateButtonBar' => new \Twig_Function_Method($this, 'generateButtonBar'),
            'generateLanguageBar' => new \Twig_Function_Method($this, 'generateLanguageBar')
        );
    }

    public function generateButtonBar(){
        $button_bar = '<div class="form-control-static pull-right" style="padding:20px 15px 0 0">
                            <input type="submit" value="Accept" class="btn btn-primary">
                            <input type="button" id="cancel" value="Cancel" class="btn btn-primary">
                        </div>';
        return $button_bar;
    }

    public function generateLanguageBar($current){
        $languages = array('es'=>'es', 'en'=>'en');
        unset($languages[$current]);
        $lang_bar = '<a data-toggle="dropdown" href="#" >
                        <span style="margin-right: 2px" class="glyphicon glyphicon-flag"></span>'.strtoupper($current).'<span class="caret"></span>
                     </a>
                     <ul class="dropdown-menu">';

        $callable = function($lang){
            $path = $this->router->generate('lang', array('_locale'=>$lang));
            return '<li><a href="'.$path.'" >'.strtoupper($lang).'</a></li>';
        };
        $lang_bar .= implode(' ', array_map($callable, $languages));
        $lang_bar .= '</ul>';
        return $lang_bar;
    }

    public function getName(){
        return 'TicketBundle';
    }
}