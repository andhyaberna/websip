<?php

class HomeController {
    public function index() {
        // Echo the view as requested
        // The view function will include the file, which in turn includes the layout
        view('home');
    }
}
