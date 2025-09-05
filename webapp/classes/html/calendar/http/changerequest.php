<?php

namespace html\calendar\http;

class ChangeRequest {
    public array $masses;
    public array $deletedMasses;

    public function __construct(array $masses, array $deletedMasses) {
        $this->masses = $masses;
        $this->deletedMasses = $deletedMasses;
    }
}